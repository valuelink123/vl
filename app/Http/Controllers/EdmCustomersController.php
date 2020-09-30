<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EdmCustomer;
use DB;
use \DrewM\MailChimp\MailChimp;
use \App\Models\EdmTag;

class EdmCustomersController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */
	use \App\Traits\DataTables;
	use \App\Traits\Mysqli;
	public function __construct()
	{
		$this->middleware('auth');
		parent::__construct();
	}

	/**
	 * Show the application dashboard
	 */
	public function index()
	{
		// $MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
		// $MailChimp->verify_ssl=false;//测试时才开启
		// $list_id = env('MAILCHIMP_LISTID', '');
		// $response = $MailChimp->post("lists/$list_id/members/tags", [
		// 	'name' => 'test',
		// 	'status' => 'active',
		// ]);
		// $pushdata['members'][] = array(
		// 	'email_address' => '15@qq.com',
		// 	'status' => 'subscribed',
		// 	'tags'=>['test3','test2']
		// 	// 'tags'=>[
		// 	// 	['name' => 'test1', 'status' => 'active'],
		// 	//
		// 	// ]
		// );
		// $response = $MailChimp->post("lists/$list_id",$pushdata);//批量添加/更新成员列表，每次最多添加/更新500个

		// $pushdata = array(
		// 	'email_address' => '13@qq.com',
		// 	'status' => 'subscribed',
		// 	'tags'=>['test1','test2']
		// 	// 'tags'=>[
		// 	// 	['name' => 'test1', 'status' => 'active',]
		// 	// ]
		// 	// 'tags'=>[
		// 	// 	['name' => 'test1', 'status' => 'active'],
		// 	//
		// 	// ]
		// );
		// $response = $MailChimp->post("lists/$list_id/members",$pushdata);//添加一个客户到mailchimp

		// echo '<pre>';
		// var_dump($response);
		// exit;

		if(!Auth::user()->can(['edm-customers-show'])) die('Permission denied -- edm-customers-show');
		$tag = EdmTag::getEdmCustomerTag();
		$status = array(0=>'active',1=>'inactive');
		$mailchimp_status = array(0=>'exist',1=>'inexist');
		return view('edm/customerIndex',['tag'=>$tag,'status'=>$status,'mailchimp_status'=>$mailchimp_status]);
	}

	/*
	 * ajax展示客户列表
	 */
	public function List()
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$email = isset($search['email']) ? urldecode($search['email']) : '';
		$status = isset($search['status']) ? $search['status'] : '';
		$mailchimp_status = isset($search['mailchimp_status']) ? $search['mailchimp_status'] : '';
		$tag_id = isset($search['tag_id']) ? $search['tag_id'] : '';

		$tagData = EdmTag::getEdmCustomerTag();
		$query = new EdmCustomer();
		if($email){
			$query = $query->where('email',$email);
		}

		if($mailchimp_status !== ''){
			$query = $query->where('mailchimp_status',$mailchimp_status);
		}
		if($status !== ''){
			$query = $query->where('status',$status);
		}
		if($tag_id){
			$query = $query->where(DB::raw("FIND_IN_SET({$tag_id},tag_id)"));
		}

		$recordsFiltered = $recordsTotal = $query->count();//计算出总数
		$iDisplayLength = intval($_REQUEST['length']);
		$iDisplayLength = $iDisplayLength < 0 ? $recordsTotal : $iDisplayLength;
		$iDisplayStart = intval($_REQUEST['start']);
		$data =  $query->orderBy('id','desc')->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();//查询数据
		foreach($data as $key=>$val){
			$tag_id = $val['tag_id'];
			$tag_ids = explode(',',$tag_id);
			$data[$key]['tag_name'] = '';
			foreach($tag_ids as $k=>$tagid){
				if(isset($tagData[$tagid])){
					$data[$key]['tag_name'].= $tagData[$tagid].',';
				}
			}
			$data[$key]['tag_name'] = rtrim($data[$key]['tag_name'],",");
			$data[$key]['mailchimp_status'] = 'exist';
			$data[$key]['status'] = 'active';
			$action = '';
			//type为1,push数据到mailchimp(mailchimp_status,1=>0);type=2,客户状态由inactive改为active(status,1=>0);type=3,客户状态由active改为inactive(status,0=>1)
			if($val['mailchimp_status']==1){
				$data[$key]['mailchimp_status'] = 'inexist';
				$action .= '<button type="button" class="status_btn btn btn-success btn-xs" data-type="1" data-id="'.$val['id'].'">Push</button><br>';
			}
			if($val['status']==1){
				$data[$key]['status'] = 'inactive';
				$action .= '<button type="button" class="status_btn btn btn-success btn-xs" data-type="2" data-id="'.$val['id'].'">Active</button>';
			}else{
				$action .= '<button type="button" class="status_btn btn btn-danger btn-xs" data-type="3" data-id="'.$val['id'].'">InActive</button>';
			}
			if(!Auth::user()->can(['edm-customers-update'])){
				$action = 'NO';
			}else{
				$action .= '<br><a href="/edm/customers/update?id='.$val['id'].'" class="btn btn-success btn-xs" >Edit</a>';
			}
			$data[$key]['action'] = $action;
		}

		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	 * 添加单个客户数据
	 */
	public function add(Request $request)
	{
		if(!Auth::user()->can(['edm-customers-add'])) die('Permission denied -- edm-customers-add');
		if($request->isMethod('get')){
			$tag = EdmTag::getEdmCustomerTag();
			return view('edm/customerAdd',['tag'=>$tag]);
		}elseif ($request->isMethod('post')){
			//push客户信息到mailchimp后台
			$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
			$MailChimp->verify_ssl=false;//测试时才开启
			$list_id = env('MAILCHIMP_LISTID', '');
			$customerData = EdmCustomer::where('email',$_POST['email'])->first();//判断此用户是否已经存在
			if($customerData){
				$request->session()->flash('error_message','此用户已存在');
				return redirect()->back()->withInput();
			}
			$configfield = array('email'=>'email_address','first_name'=>'first_name','last_name'=>'last_name','address'=>'address','phone'=>'phone_number','tag_id'=>'tag_id');
			$pushdata['status'] = 'subscribed';
			$insertData = array();
			foreach($configfield as $key=>$field){
				if(isset($_POST[$key]) && $_POST[$key]){
					if($key=='tag_id'){
						$insertData[$key] = implode(',',$_POST[$key]);
					}else{
						$insertData[$key] = $_POST[$key];
						$pushdata[$field] = $_POST[$key];
					}
				}
			}
			$response = $MailChimp->post("lists/$list_id/members",$pushdata);//添加一个客户到mailchimp
			if($response){
				if(isset($response['detail'])){
					$insertData['error_info'] = $response['detail'];
					$insertData['mailchimp_status'] = 1;
				}
			}else{
				$insertData['mailchimp_status'] = 1;
			}
			EdmCustomer::insertOnDuplicateWithDeadlockCatching($insertData,['email']);//数据插入edm_customer表中
		}
		return redirect('/edm/customers');
	}

	/*
	 * 更新单个客户数据
	 */
	public function update(Request $request)
	{
		if(!Auth::user()->can(['edm-customers-update'])) die('Permission denied -- edm-customers-update');
		if($request->isMethod('get')){
			$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : '';
			$tag = EdmTag::getEdmCustomerTag();
			$customerData = EdmCustomer::where('id',$id)->first();
			if($customerData){
				$customerData = $customerData->toArray();
				$customerData['tag_ids'] = explode(',',$customerData['tag_id']);
				return view('edm/customerEdit',['tag'=>$tag,'customerData'=>$customerData]);
			}else{
				$request->session()->flash('error_message','ID error');
				return redirect()->back()->withInput();
			}
		}elseif ($request->isMethod('post')){
			$id = isset($_POST['id']) && $_POST['id'] ? $_POST['id'] : '';
			if(isset($_POST['tag_id']) && $_POST['tag_id']){
				$update['tag_id'] = implode(',',$_POST['tag_id']);
			}else{
				$update['tag_id'] = '';
			}
			$res = EdmCustomer::where('id',$id)->update($update);
			if($res){
				return redirect('/edm/customers');
			}else{
				$request->session()->flash('error_message','Update Failed');
				return redirect()->back()->withInput();
			}
		}
	}

	/*
	 * 导入excel表格数据到CRM模块
	 */
	public function import( Request $request )
	{
		if(!Auth::user()->can(['edm-customers-add'])) die('Permission denied -- edm-customers-add');
		$addnum = 0;
		set_time_limit(0);
		if($request->isMethod('POST')){
			$file = $request->file('importFile');
			if($file){
				if($file->isValid()){
					$ext = $file->getClientOriginalExtension();
					$newname = date('Y-m-d-H-i-s').'-'.uniqid().'.'.$ext;
					$newpath = '/uploads/edm_customer/'.date('Ymd').'/';
					$inputFileName = public_path().$newpath.$newname;
					$bool = $file->move(public_path().$newpath,$newname);

					if($bool){
						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
						//得到的数据中,A=>email,B=>first_name,C=>last_name,D=>address,E=>phone,F=>tag_id
						$insertData = $mailchimp_data = array();
						foreach($importData as $key => $val){
							if($key==1 || empty($val['A'])){
								unset($importData[$key]);
								continue;
							}

							$insertData[$val['A']] = array(
								'email' => $val['A'],
								'first_name' => $val['B'],
								'last_name' => $val['C'],
								'address' => $val['D'],
								'phone' => $val['E'],
								'tag_id' => $val['F'],
								'error_info' => '',
								'mailchimp_status' => 0,
							);
							$mailchimp_data[] = array(
								'email_address' => $val['A'],
								'status' => 'subscribed',
								// 'tags' => 'vop_edm',
								'first_name' => $val['B'],
								'last_name' => $val['C'],
								'address' => $val['D'],
								'phone_number' => $val['E'],
							);
						}
						// EdmCustomer::insertOnDuplicateWithDeadlockCatching($insertData,['email']);//数据插入edm_customer表中
						//把客户信息数据推送到mailchimp中
						$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
						$MailChimp->verify_ssl=false;//测试时才开启
						$list_id = env('MAILCHIMP_LISTID', '');
						$mailchimp_data = array_chunk($mailchimp_data,500);//把数组分成每500个一组,分批处理
						foreach($mailchimp_data as $key=>$val){
							$pushdata['members'] = $val;//推送到mailchimp中的数据要以members为键
							$pushdata['tags'] = 'test';
							 $response = $MailChimp->post("lists/$list_id",$pushdata);//批量添加/更新成员列表，每次最多添加/更新500个
							//错误代码为ERROR_CONTACT_EXISTS时,表示mailchimp列表中已存在
							$errorInfo = $response['errors'];//API接口返回的错误信息
							foreach($errorInfo as $k=>$v){
								if($v['error_code']!='ERROR_CONTACT_EXISTS'){
									$insertData[$v['email_address']]['email'] = $v['email_address'];
									$insertData[$v['email_address']]['error_info'] = $v['error'];
									$insertData[$v['email_address']]['mailchimp_status'] = 1;
								}
							}
						}
						$insertData = array_values($insertData);

						EdmCustomer::insertOnDuplicateWithDeadlockCatching($insertData,['email']);//数据插入edm_customer表中

						if (!$MailChimp->success()) {
							print_r($MailChimp->getLastError());
							print_r($MailChimp->getLastResponse());
							die();
						}

						$request->session()->flash('success_message','Import Data Success!');
					}else{
						$request->session()->flash('error_message','Import Data Failed');
					}
				}else{
					$request->session()->flash('error_message','Import Data Failed,The file is too large');
					// return redirect()->back()->withInput();
				}
			}else{
				$request->session()->flash('error_message','Please Select Upload File');
				// return redirect()->back()->withInput();
			}
		}
		return redirect('/edm/customers');
	}

	/*
	 * 下载导入excel表格的模板
	 */
	public function download(Request $request)
	{
		if(!Auth::user()->can(['edm-customers-add'])) die('Permission denied -- edm-customers-add');
		$filepath = 'edm_customer_import_template.xls';
		$file=fopen($filepath,"r");
		header("Content-type:text/html;charset=utf-8");
		header("Content-Type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".filesize($filepath));
		header("Content-Disposition: attachment; filename=".$filepath);
		echo fread($file,filesize($filepath));
		fclose($file);
	}

	//type为1,push数据到mailchimp(mailchimp_status,1=>0);type=2,客户状态由inactive改为active(status,1=>0);type=3,客户状态由active改为inactive(status,0=>1)
	public function action()
	{
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
		$return['status'] = 0;
		$return['msg'] = '更新异常';
		$update['updated_at'] = date('Y-m-d H:i:s');
		if($type==1){
			//push客户信息到mailchimp后台
			$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
			$MailChimp->verify_ssl=false;//测试时才开启
			$list_id = env('MAILCHIMP_LISTID', '');
			$customerData = EdmCustomer::where('id',$id)->first()->toArray();
			$pushdata = array(
				'email_address' => $customerData['email'],
				'status' => 'subscribed',
				'first_name' => $customerData['first_name'],
				'last_name' => $customerData['last_name'],
				'address' => $customerData['address'],
				'phone_number' => $customerData['phone'],
			);
			//推送到mailchimp中的数据要以members为键
			$response = $MailChimp->post("lists/$list_id/members",$pushdata);//批量添加/更新成员列表，每次最多添加/更新500个exit;
			if($response){
				if(isset($response['id'])){//请求成功
					$update['mailchimp_status'] = 0;
				}elseif($response['detail']){
					$update['error_info'] = $response['detail'];
					$return['msg'] =  $response['detail'];
					return $return;
				}
			}else{
				$return['msg'] = 'Push异常';
				return $return;
			}
		}elseif($type==2){
			$update['status'] = 0;
		}elseif($type==3){
			$update['status'] = 1;
		}
		if($type && $id){
			$res = EdmCustomer::where('id',$id)->update($update);
			if($res){
				$return['status'] = 1;
			}
		}
		return $return;
	}


}