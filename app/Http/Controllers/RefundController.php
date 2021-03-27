<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RefundController extends Controller
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
//		if(!Auth::user()->can(['refund-show'])) die('Permission denied -- refund-show');
		$data['account'] = $this->getAccountInfo();//得到账号机的信息
		$data['fromDate'] = date('Y-m-d',time()-2*86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
//		$data['fromDate'] = '2021-01-15';//测试日期
		return view('sales/refundIndex',['data'=>$data]);
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
			$data[$key]['asins'] = '<span title="'.$val['asins'].'">'.$val['asins'].'</span>';
			$data[$key]['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			$data[$key]['date'] = 'Refund:'.$val['posted_date'];
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	 * 导出功能
	 */
	public function export()
	{
//		if(!Auth::user()->can(['refund-export'])) die('Permission denied -- refund-export');
		$sql = $this->getSql($_GET);
		$data = DB::connection('amazon')->select($sql);
		$data = json_decode(json_encode($data),true);

		$arrayData = array();
		$headArray[] = 'ID';
		$headArray[] = 'Account';
		$headArray[] = 'Amazon Order ID';
		$headArray[] = 'Date';
		$headArray[] = 'Asin';
		$headArray[] = 'Refund Amounts';
		$headArray[] = 'Refund Commission';
		$headArray[] = 'Currency';
		$headArray[] = 'Settlement ID';
		$headArray[] = 'Settlement Date';
		$arrayData[] = $headArray;

		$accounts = $this->getAccountInfo();//得到账号机的信息
		foreach($data as $key=>$val) {
			$val['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			$val['date'] = 'Refund:'.$val['posted_date'];
			$arrayData[] = array(
				$val['id'],
				$val['account'],
				$val['amazon_order_id'],
				$val['date'],
				$val['asins'],
				$val['refund_amount'],
				$val['refund_commission'],
				$val['currency'],
				$val['settlement_id'],
				$val['settlement_date'],
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
			header('Content-Disposition: attachment;filename="Export_Refund_List.xlsx"');//告诉浏览器输出浏览器名称
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
		$where = " where posted_date >= '".$search['from_date']." 00:00:00' and posted_date <= '".$search['to_date']." 23:59:59'";
		if(isset($search['account']) && $search['account']){
			$where.= " and finances_refund_events.current_seller_account_id in (".$search['account'].")";
		}
		if(isset($search['amazon_order_id']) && $search['amazon_order_id']){
			$where.= " and finances_refund_events.amazon_order_id = '".$search['amazon_order_id']."'";
		}
		if(isset($search['currency']) && $search['currency']){
			$where.= " and finances_refund_events.currency = '".$search['currency']."'";
		}
		if(isset($search['asin']) && $search['asin']){
			$where.= " and orders.asins like '%".$search['asin']."%'";
		}
		if(isset($search['settlement_id']) && $search['settlement_id']){
			$where.= " and settlement_id = '".$search['settlement_id']."'";
		}
		if(isset($search['settlement_date']) && $search['settlement_date']){
			$where.= " and settlement_date >= '".$search['settlement_date']." 00:00:00 ' and settlement_date<= '".$search['settlement_date']." 23:59:59'";
		}
		//站点权限
		$domain = substr(getDomainBySite($search['site']), 4);//orders.sales_channel
		$where .= " and finances_refund_events.marketplace_name = '".ucfirst($domain)."'";

		$sql = "select SQL_CALC_FOUND_ROWS any_value(orders.id) as id,finances_refund_events.current_seller_account_id as seller_account_id,finances_refund_events.amazon_order_id,any_value(posted_date) as posted_date,any_value(currency) as currency,any_value(settlement_id) as settlement_id,any_value(settlement_date) as settlement_date,any_value(orders.asins) as asins,sum(CASE `type` WHEN 'RefundCommission' THEN finances_refund_events.amount ELSE 0 END) as refund_commission,sum(CASE `type` WHEN 'RefundCommission' THEN 0 ELSE finances_refund_events.amount END) as refund_amount    
				from finances_refund_events 
				left join orders on orders.seller_account_id = finances_refund_events.current_seller_account_id and orders.amazon_order_id = finances_refund_events.amazon_order_id 
				left join (
					select any_value(amazon_settlement_details.seller_account_id) as seller_account_id,amazon_settlement_details.order_id as order_id,any_value(amazon_settlements.settlement_id) as settlement_id,any_value(amazon_settlements.deposit_date) as settlement_date 
					from amazon_settlement_details 
					left join amazon_settlements on amazon_settlement_details.settlement_id = amazon_settlements.settlement_id 
					where transaction_type = 'Refund' and amazon_settlements.deposit_date >= '{$search['from_date']} 00:00:00'
					group by amazon_settlement_details.order_id,amazon_settlement_details.seller_account_id 
				) as settlement on finances_refund_events.amazon_order_id=settlement.order_id and finances_refund_events.current_seller_account_id = settlement.seller_account_id
				 {$where}
				group by finances_refund_events.current_seller_account_id,finances_refund_events.amazon_order_id,finances_refund_events.seller_sku 
				order by posted_date desc";
		return $sql;
	}

}