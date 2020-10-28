<?php

namespace App\Http\Controllers;

use App\AsinSalesPlan;
use App\DailyStatistic;
use App\InternationalTransportTime;
use App\SapPurchase;
use App\SapPurchaseRecord;
use App\SapSkuSite;
use App\ShipmentRequest;
use Illuminate\Http\Request;
use \App\Models\AsinPlansPlan;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;
use App\Models\AsinData;
class TransferRequestController extends Controller
{

	use \App\Traits\Mysqli;
	use \App\Traits\DataTables;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */

	public function __construct()
	{
		$this->middleware('auth');
		parent::__construct();
	}
	//列表功能
	public function list(Request $req)
	{
		if(!Auth::user()->can(['transfer-request-show'])) die('Permission denied -- transfer-request-show');
		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$date_to = isset($search['date_from']) && $search['date_from'] ? $search['date_from'] : date('Y-m-d');//默认时间范围，最近30天
		$date_from = isset($search['date_to']) && $search['date_to'] ? $search['date_to'] : date('Y-m-d',strtotime("-29 day"));
		$operaStatus = array(1=>array('BU通过','blue-madison'),2=>array('BG通过','blue-madison'),6=>array('计划确认','blue-madison'),3=>array('BU退回','red-sunglo'),4=>array('BG退回','red-sunglo'),5=>array('计划退回','red-sunglo'),7=>array('关闭','red-sunglo'));//批量操作状态
		if ($req->isMethod('GET')) {
			return view('transfer/requestList', ['date_from'=>$date_from,'date_to'=>$date_to,'bgs'=>$this->getBgs(),'bus'=>$this->getBus(),'operaStatus'=>$operaStatus]);
		}

		$searchField = array('date_from'=>array('>='=>'transfer_requests.created_at'),'date_to'=>array('<='=>'transfer_requests.created_at'),'site'=>'transfer_requests.marketplace_id','account'=>'transfer_requests.seller_id','bg'=>'transfer_requests.bg','bu'=>'transfer_requests.bu','user_id'=>'transfer_requests.user_id','status'=>'transfer_requests.status','asin'=>'transfer_requests.asin','item_no'=>'transfer_requests.sku');

		$where = $this->getSearchWhereSql($search,$searchField);
		// $orderby = $this->dtOrderBy($req);
		$sql = "select SQL_CALC_FOUND_ROWS transfer_requests.*, transfer_plans.status as plan_status,transfer_plans.require_attach as require_attach 
				from transfer_requests 
				left join transfer_plans on transfer_request_id = transfer_requests.id
				where 1=1 {$where} order by transfer_requests.id desc";
		if($req['length'] != '-1'){//等于-1时为查看全部的数据
			$limit = $this->dtLimit($req);
			$sql .= " LIMIT {$limit} ";
		}

		$datas = DB::select($sql);
		$data = json_decode(json_encode($datas),true);
		$recordsTotal = $recordsFiltered = (DB::select('SELECT FOUND_ROWS() as count'))[0]->count;
		$status = transferRequestStatus();//状态枚举
		$userIdName = $this->getUsersIdName();//所有用户的idname对应数据
		$sellerAccount = getAccountIdName();//账号id跟账号名称对应表
		$siteCode = getSiteCode();//站点代码键值对，例如'US' =>'ATVPDKIKX0DER',
		$userInfo = Auth::user();//登录用户信息

		foreach ($data as $key => $val) {
			$data[$key]['id'] = '<input type="checkbox" class="checkbox-item" name="checkedInput" data-sku="'.$val['sku'].'" data-asin="'.$val['asin'].'" data-status="'.$val['status'].'" value="'.$val['id'].'">';
			$data[$key]['site'] = array_search($val['marketplace_id'], $siteCode);
			$data[$key]['status_name'] = isset($status[$val['status']]) && $status[$val['status']] ? $status[$val['status']] : $val['status'];
			$data[$key]['seller_name'] = isset($userIdName[$val['user_id']]) && $userIdName[$val['user_id']] ? $userIdName[$val['user_id']] : $val['user_id'];
			$data[$key]['account'] = isset($sellerAccount[$val['seller_id']]) && $sellerAccount[$val['seller_id']] ? $sellerAccount[$val['seller_id']] : $val['seller_id'];
			$id = $val['id'];
			$data[$key]['action'] = '<a href="/transfer/request/edit?id='.$id.'">Show</a>';
			if($val['status'] == 0 && $val['user_id'] == $userInfo->id){//状态为0并且自己只能更改自己添加的申请
				$data[$key]['action'] = '<a href="/transfer/request/edit?id='.$id.'">Edit</a>';
			}
			if($val['plan_status']==1 && $val['require_attach']==1 && $val['user_id'] == $userInfo->id && $val['attach_data']==''){
				//计划状态为已审核，并且需要大货资料
				$data[$key]['action'] .= '<br><button style="width:62px" data-id="'.$val['id'].'" class="up-attach">上传资料</button>';
			}
		}

		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	//添加调拨申请
	public function add(Request $request)
	{
		if(!Auth::user()->can(['transfer-request-add'])) die('Permission denied -- transfer-request-add');
		if($request->isMethod('get')){
			return view('transfer/requestAdd',['date'=>date('Y-m-d')]);
		}elseif ($request->isMethod('post')){
			$insertData['marketplace_id'] = isset($_POST['site']) && $_POST['site'] ? $_POST['site'] : '';
			$insertData['seller_id'] = isset($_POST['account']) && $_POST['account'] ? $_POST['account'] : '';
			$insertData['delivery_date'] = isset($_POST['date']) && $_POST['date'] ? $_POST['date'] : '';
			$insertData['request_reason'] = isset($_POST['request_reason']) && $_POST['request_reason'] ? $_POST['request_reason'] : '';
			$site = isset($_POST['site']) && $_POST['site'] ? $_POST['site'] : '';

			$userInfo = Auth::user();
			$insertData['sap_seller_id'] = $userInfo->sap_seller_id;
			$insertData['user_id'] = $userInfo->id;
			$insertData['bg'] = $userInfo->ubg;
			$insertData['bu'] = $userInfo->ubu;
			$insertData['created_at'] = $insertData['updated_at'] = date('Y-m-d H:i:s');
			//得到此用户的今天最近一个请求号
			$requestNum = DB::table('transfer_requests')->where('transfer_request_key','like',date('Ymd').'%')->orderBy('created_at','desc')->pluck('transfer_request_key')->first();
			$insertData['transfer_request_key'] = $requestNum ? $requestNum+1 : date('Ymd').'0001';

			$num = 0;
			if(isset($_POST['group-data']) && $_POST['group-data']){
				$siteUrl = getSiteUrl();
				$url = isset($siteUrl[$site]) && $siteUrl[$site] ? 'www.'.$siteUrl[$site] : $site;
				foreach($_POST['group-data'] as $key=>$val){
					$insertData['asin'] = $val['asin'];
					$insertData['quantity'] = $val['quantity'];
					//查出此站点此asin的sku
					$sku = DB::table('asin')->where('asin',$val['asin'])->where('site',$url)->pluck('item_no')->first();
					$insertData['sku'] = $sku ? $sku : '';

					//添加表的数据
					$resId = DB::table('transfer_requests')->insertGetId($insertData);
					if($resId){
						SaveOperationLog('transfer_requests', $resId, $insertData);//添加操作存日志
						$num++;
					}
				}
			}
			if($num==0){
				$request->session()->flash('error_message','Add Failed');
				return redirect()->back()->withInput();
			}
		}
		return redirect('/transfer/request/list');
	}

	//展示某个asin的情况
	public function edit(Request $request)
	{
		$userInfo = Auth::user();//登录用户信息
		if(!$userInfo->can(['transfer-request-show'])) die('Permission denied -- transfer-request-show');
		$id = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] : '';
		$data = DB::table('transfer_requests')->where('id',$id)->first();//获取此id的数据
		if(empty($data)){
			$request->session()->flash('error_message','ID error');
			return redirect()->back()->withInput();
		}else{
			$data = (array)$data;
		}
		$type = 1;//只展示不可修改
		if($data['status'] == 0 && $data['user_id'] == $userInfo->id){
			$type = 2;//可修改
		}

		if($request->isMethod('get')){
			return view('transfer/requestEdit',['type'=>$type,'showtype'=>$type==1 ? 'disabled="disabled"' : '','data'=>$data]);
		}elseif ($request->isMethod('post')){
			if($type==1){
				$request->session()->flash('error_message','permissions error');
				return redirect()->back()->withInput();
			}
			$updateData['marketplace_id'] = isset($_POST['site']) && $_POST['site'] ? $_POST['site'] : '';
			$updateData['seller_id'] = isset($_POST['account']) && $_POST['account'] ? $_POST['account'] : '';
			$updateData['delivery_date'] = isset($_POST['date']) && $_POST['date'] ? $_POST['date'] : '';
			$updateData['request_reason'] = isset($_POST['request_reason']) && $_POST['request_reason'] ? $_POST['request_reason'] : '';
			$site = isset($_POST['site']) && $_POST['site'] ? $_POST['site'] : '';
			$updateData['updated_at'] = date('Y-m-d H:i:s');

			if(isset($_POST['group-data']) && $_POST['group-data']){
				$siteUrl = getSiteUrl();
				$url = isset($siteUrl[$site]) && $siteUrl[$site] ? 'www.'.$siteUrl[$site] : $site;
				foreach($_POST['group-data'] as $key=>$val){
					$updateData['asin'] = $val['asin'];
					$updateData['quantity'] = $val['quantity'];
					//查出此站点此asin的sku
					$sku = DB::table('asin')->where('asin',$val['asin'])->where('site',$url)->pluck('item_no')->first();
					$updateData['sku'] = $sku ? $sku : '';
					//更新表的数据
					$res = DB::table('transfer_requests')->where('id',$id)->update($updateData);
					if($res){
						SaveOperationLog('transfer_requests', $id, $updateData);//添加操作存日志
						$request->session()->flash('success_message','update Success');
					}else{
						$request->session()->flash('error_message','update Failed');
					}
				}
			}

		}
		return redirect()->back()->withInput();
	}

	//更新状态，审核调货请求
	public function updateStatus(Request $request){
		$ids = isset($_POST['ids']) ? $_POST['ids'] : '';
		$updateData['status'] = isset($_POST['status']) ? $_POST['status'] : '';
		$updateData['updated_at'] = date('Y-m-d H:i:s');
		$id_array = explode(',',$ids);
		if($ids && $updateData['status']){
			$res = DB::table('transfer_requests')->whereIn('id',$id_array)->update($updateData);
			if($res){
				foreach($id_array as $id){
					SaveOperationLog('transfer_requests', $id, $updateData);//添加操作存日志
				}
				return $res;
			}
		}
		return 0;
	}
	//上传大货资料
	public function uploadAttach(Request $request)
	{
		$userInfo = Auth::user();//登录用户信息
		if(!$userInfo->can(['transfer-request-update'])) die('Permission denied -- transfer-request-update');
		$id = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] : '';
		$data = DB::table('transfer_requests')->where('id',$id)->first();//获取此id的数据
		if(empty($data)){
			$request->session()->flash('error_message','ID error');
			return redirect()->back()->withInput();
		}
		$file = $request->file('uploadFile');
		if ($file) {
			if ($file->isValid()) {
				$originalName = $file->getClientOriginalName();
				$newpath = '/uploads/requestAttach/' . date('Ymd') . '/';
				$newname = time().'__'.$originalName;//文件名称加个时间戳，这样子不同销售上传的文件名可以一样
				$bool = $file->move(public_path() . $newpath, $newname);

				if($bool){
					//更新表的数据
					$updateData['updated_at'] = date('Y-m-d H:i:s');
					$updateData['attach_data'] = $newpath.$newname;
					$res = DB::table('transfer_requests')->where('id',$id)->update($updateData);
					if($res){
						SaveOperationLog('transfer_requests', $id, $updateData);//添加操作存日志
						$request->session()->flash('success_message','upload success');
						return redirect()->back()->withInput();
					}
				}

			}
		}
		$request->session()->flash('error_message','upload failed');
		return redirect()->back()->withInput();
	}
	//下载查看大货资料
	public function downloadAttach(Request $request)
	{
		$userInfo = Auth::user();//登录用户信息
		if(!$userInfo->can(['transfer-request-update'])) die('Permission denied -- transfer-request-update');
		$id = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] : '';
		$data = DB::table('transfer_requests')->where('id',$id)->select('attach_data')->first();//获取此id的数据
		if(empty($data)){
			$request->session()->flash('error_message','ID error');
			return redirect()->back()->withInput();
		}
		$array = explode('/',$data->attach_data);
		$filename = explode('__',end($array));//$filename为文件名称
		unset($filename[0]);
		$filename = implode('',$filename);//重组filename,让下载的文件名称跟上传时的名称一致
		$filepath = ltrim($data->attach_data,'/');//去掉最左侧的/
		$file=fopen($filepath,"r");//打开文件

		header("Content-type:text/html;charset=utf-8");
		header("Content-Type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".filesize($filepath));
		header("Content-Disposition: attachment; filename=".$filename);
		echo fread($file,filesize($filepath));
		fclose($file);
	}

}