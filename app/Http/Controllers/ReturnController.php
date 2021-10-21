<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReturnController extends Controller
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
		if(!Auth::user()->can(['return-show'])) die('Permission denied -- return-show');
		$data['account'] = $this->getAccountInfo();//得到账号机的信息
		$data['fromDate'] = date('Y-m-d',time()-2*86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
//		$data['fromDate'] = '2021-01-15';//测试日期
		return view('sales/returnIndex',['data'=>$data]);
	}

	/*
	 * ajax展示订单列表
	 */
	public function List(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$sql = $this->getSql($search);

		if($req['length'] != '-1'){//等于-1时为查看全部的数据
			$limit = $this->dtLimit($req);
			$sql .= " LIMIT {$limit} ";
		}
		$datas = DB::connection('amazon')->select($sql);
		$data = json_decode(json_encode($datas),true);
		$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
		$accounts = $this->getAccountInfo();//得到账号机的信息
		foreach($data as $key=>$val) {
			$data[$key]['customer_comments'] = '<span title="'.$val['customer_comments'].'">'.$val['customer_comments'].'</span>';
			$data[$key]['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			$data[$key]['date'] = 'Return:'.$val['return_date'];
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	 * 导出功能
	 */
	public function export()
	{
		if(!Auth::user()->can(['return-export'])) die('Permission denied -- return-export');
		$sql = $this->getSql($_GET);
		$data = DB::connection('amazon')->select($sql);
		$data = json_decode(json_encode($data),true);

		$arrayData = array();
		$headArray[] = 'ID';
		$headArray[] = 'Account';
		$headArray[] = 'Amazon Order ID';
		$headArray[] = 'Date';
		$headArray[] = 'Asin';
		$headArray[] = 'Seller SKU';
		$headArray[] = 'Quantity';
		$headArray[] = 'Status';
		$headArray[] = 'Reason';
		$headArray[] = 'Condition';
		$headArray[] = 'Customer Comments';
		//$headArray[] = 'Settlement ID';
		//$headArray[] = 'Settlement Date';
		$arrayData[] = $headArray;

		$accounts = $this->getAccountInfo();//得到账号机的信息
		foreach($data as $key=>$val) {
			$val['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			$val['date'] = 'Return:'.$val['return_date'];
			$arrayData[] = array(
				$val['id'],
				$val['account'],
				$val['amazon_order_id'],
				$val['date'],
				$val['asin'],
				$val['seller_sku'],
				$val['quantity'],
				$val['status'],
				$val['reason'],
				$val['condition'],
				$val['customer_comments'],
				//$val['settlement_id'],
				//$val['settlement_date'],
			);
		}

		if($arrayData){
			$spreadsheet = new Spreadsheet();
			$spreadsheet->getActiveSheet()
				->fromArray(
					$arrayData,  // The data to set
					NULL,        // Array values with this value will not be set
					'A1'         // Top left coordinate of the worksheet range where
				//    we want to set these values (default is A1)
				);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
			header('Content-Disposition: attachment;filename="Export_Return_List.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}

	//获得搜索条件并且返回对应的sql语句
	public function getSql($search)
	{
		//搜索条件如下：from_date,to_date,account,status,amazon_order_id,asin,tracking_no,carry_code,settlement_id,settlement_date
		$where = " where return_date >= '".$search['from_date']." 00:00:00' and return_date <= '".$search['to_date']." 23:59:59'";
		if(isset($search['account']) && $search['account']){
			$where.= " and amazon_returns.seller_account_id in (".$search['account'].")";
		}else{
			//站点权限
			$data= DB::connection('amazon')->select("select id,label from seller_accounts where deleted_at is NULL and mws_marketplaceid = '{$search['site']}' order by label asc");
			if($data){
				$accountStr = '';
				foreach($data as $key=>$val){
					$accountStr .= $val->id.',';
				}
				$accountStr = rtrim($accountStr,',');
				$where.= " and amazon_returns.seller_account_id in (".$accountStr.")";
			}
		}
		if(isset($search['amazon_order_id']) && $search['amazon_order_id']){
			$where.= " and amazon_returns.amazon_order_id = '".$search['amazon_order_id']."'";
		}

		if(isset($search['asin']) && $search['asin']){
			$where.= " and amazon_returns.asin like '%".$search['asin']."%'";
		}
		if(isset($search['status']) && $search['status']){
			$where.= " and amazon_returns.status = '".$search['status']."'";
		}
		if(isset($search['reason']) && $search['reason']){
			$where.= " and reason = '".$search['reason']."'";
		}
		if(isset($search['condition']) && $search['condition']){
			$where.= " and detailed_disposition = '".$search['condition']."'";
		}
		if(isset($search['seller_sku']) && $search['seller_sku']){
			$where.= " and amazon_returns.seller_sku = '".$search['seller_sku']."'";
		}
		if(isset($search['settlement_id']) && $search['settlement_id']){
			//$where.= " and settlement_id = '".$search['settlement_id']."'";
		}

		if (Auth::user()->seller_rules) {
			$rules = explode("-",Auth::user()->seller_rules);
			if(array_get($rules,0)!='*') $where.= " and tb.sap_seller_bg='".array_get($rules,0)."'";
			if(array_get($rules,1)!='*') $where.= " and tb.sap_seller_bu='".array_get($rules,1)."'";
		} elseif (Auth::user()->sap_seller_id) {
			$where.= " and tb.sap_seller_id=".Auth::user()->sap_seller_id;
		}

		$sql = "select SQL_CALC_FOUND_ROWS amazon_returns.id as id,amazon_returns.seller_account_id as seller_account_id,amazon_returns.amazon_order_id as amazon_order_id,amazon_returns.return_date as return_date,amazon_returns.asin as asin,amazon_returns.seller_sku as seller_sku,amazon_returns.quantity as quantity,amazon_returns.status as status,amazon_returns.reason as reason,amazon_returns.customer_comments as customer_comments,amazon_returns.detailed_disposition as `condition`  
			from amazon_returns 
			left join seller_accounts 
			on `amazon_returns`.`seller_account_id` = seller_accounts.id
			left join sap_asin_match_sku as tb 
			on amazon_returns.seller_sku=tb.seller_sku and seller_accounts.mws_seller_id=tb.seller_id and seller_accounts.mws_marketplaceid=tb.marketplace_id
				{$where}";
		return $sql;
	}

}