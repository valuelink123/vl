<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use DB;

class AmazonFulfiledShipmentsController extends Controller
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
		if(!Auth::user()->can(['amazon-fulfilled-shipments-report'])) die('Permission denied -- amazon-fulfilled-shipments-report');
		$accounts_data = DB::connection('vlz')->table('seller_accounts')->where('primary',1)->whereNull('deleted_at')->pluck('label','id');
		$data['fromDate'] = date('Y-m-d',time()-7*86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
//		$data['fromDate'] = '2020-06-07';//测试日期
		return view('reports/amazon_fulfiled_shipments',['data'=>$data,'accounts_data'=>$accounts_data]);
	}

	/*
	 * ajax展示订单列表
	 */
	public function List(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));

		//搜索条件如下：
		$where = " where from_date >= '".$search['from_date']."' and to_date <= '".$search['to_date']."'";
		if(isset($search['account']) && $search['account']){
			$where.= " and seller_account_id like %".$search['account']."%";
		}

		$sql = "select SQL_CALC_FOUND_ROWS * 
				from report 
				{$where}
				order by id desc";

		if($req['length'] != '-1'){//等于-1时为查看全部的数据
			$limit = $this->dtLimit($req);
			$sql .= " LIMIT {$limit} ";
		}
		$datas = DB::select($sql);
		$data = json_decode(json_encode($datas),true);

		$recordsTotal = $recordsFiltered = (DB::select('SELECT FOUND_ROWS() as count'))[0]->count;
		$accounts = $this->getAccountInfo();//得到账号机的信息

		$statusArray = array(0=>'未完成',1=>'成功',2=>'失败');
		foreach($data as $key=>$val) {
			$seller_account_id = explode(",",$val['seller_account_id']);
			$data[$key]['account'] = '';
			if($seller_account_id){
				foreach($seller_account_id as $account_id){
					$account_name = isset($accounts[$account_id]) ? $accounts[$account_id]['label'] : $account_id;
					if($account_name){
						$data[$key]['account'] .= $account_name.';';
					}
				}
			}

			$data[$key]['action'] = '-';
			if($data[$key]['status']==1){
				$data[$key]['action'] = '<a href="/amazonFulfiledShipments/download?url='.$val['file_path'].'">下载</a>';
			}
			$data[$key]['status'] =isset($statusArray[$val['status']]) ? $statusArray[$val['status']] : $val['status'];
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	 * 生成报表
	 */
	public function export()
	{
		if(!Auth::user()->can(['amazon-fulfilled-shipments-report'])) die('Permission denied -- amazon-fulfilled-shipments-report');

		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
//		echo '<pre>';
//		var_dump($search,$_REQUEST['account']);
//		exit;


		$insertData = array(
			'from_date'=> $search['from_date'],
			'to_date'=> $search['to_date'],
			'seller_account_id' => $_REQUEST['account'],
			'file_path' => '/report_file/'.date('Y-m-d').'/'.time().'.xls',
			'status' => 0,
			'user_id' => Auth::user()->id,
		);

		$id = DB::table('report')->insertGetId($insertData);

		if($id){
//			Artisan::call("make:report", ['id' => $id]);
			Artisan::call("make:report");

			return array('status'=>1,'msg'=>'操作成功');
		}else{
			return array('status'=>0,'msg'=>'操作失败');
		}


	}

	/*
	 * 下载报表
	 */
	public function download()
	{
		$filepath = isset($_GET['url']) ? $_GET['url'] : '';
		$filepath = public_path().$filepath;
		return response()->download($filepath);
	}

}