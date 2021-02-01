<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class McfOrderListController extends Controller
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
		return view('sales/mcfOrderListIndex',['data'=>$data]);
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
			$data[$key]['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			$data[$key]['date'] = 'Order:'.$val['mcforder_date'];
			$data[$key]['tracking_no'] = '/NA';
			$data[$key]['carrier_code'] = '/NA';
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
		$headArray[] = 'Seller SKU';
		$headArray[] = 'Order Status';
		$headArray[] = 'Customer Name';
		$headArray[] = 'Country';
		$headArray[] = 'Shipping Speed';
		$headArray[] = 'Tracking No.';
		$headArray[] = 'Carrier Code';
		$headArray[] = 'Settlement ID';
		$headArray[] = 'Settlement Date';
		$arrayData[] = $headArray;

		$accounts = $this->getAccountInfo();//得到账号机的信息
		foreach($data as $key=>$val) {
			$val['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			$val['date'] = 'Order:'.$val['mcforder_date'];
			$val['tracking_no'] = '/NA';
			$val['carrier_code'] = '/NA';
			$arrayData[] = array(
				$val['id'],
				$val['account'],
				$val['amazon_order_id'],
				$val['date'],
				$val['seller_sku'],
				$val['order_status'],
				$val['customer_name'],
				$val['country'],
				$val['shipping_speed'],
				$val['tracking_no'],
				$val['carrier_code'],
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
			header('Content-Disposition: attachment;filename="Export_McfOrder_List.xlsx"');//告诉浏览器输出浏览器名称
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
		$where = " where displayable_order_date_time >= '".$search['from_date']." 00:00:00' and displayable_order_date_time <= '".$search['to_date']." 23:59:59'";
		if(isset($search['account']) && $search['account']){
			$where.= " and t1.seller_account_id in (".$search['account'].")";
		}
		if(isset($search['amazon_order_id']) && $search['amazon_order_id']){
			$where.= " and t1.seller_fulfillment_order_id = '".$search['amazon_order_id']."'";
		}

		if(isset($search['status']) && $search['status']){
			$where.= " and t1.fulfillment_order_status = '".$search['status']."'";
		}
		if(isset($search['seller_sku']) && $search['seller_sku']){
			$where.= " and t1.seller_skus like '%".$search['seller_sku']."%'";
		}
		if(isset($search['customer_name']) && $search['customer_name']){
			$where.= " and t1.name like '%".$search['customer_name']."%'";
		}

		$sql = "select SQL_CALC_FOUND_ROWS t1.id as id,t1.seller_account_id as seller_account_id,t1.seller_fulfillment_order_id as amazon_order_id,
t1.displayable_order_date_time as mcforder_date,t1.shipping_speed_category as shipping_speed,t1.name as customer_name,
t1.country_code as country,t1.seller_skus as seller_sku,t1.fulfillment_order_status as order_status,
settlement_id,settlement_date  
			from amazon_mcf_orders as t1 
			left join (
					select any_value(amazon_settlement_details.seller_account_id) as seller_account_id,amazon_settlement_details.order_id as order_id,
any_value(amazon_settlements.settlement_id) as settlement_id,any_value(amazon_settlements.deposit_date) as settlement_date 
					from amazon_settlement_details 
					left join amazon_settlements on amazon_settlement_details.settlement_id = amazon_settlements.settlement_id 
					where amazon_settlements.deposit_date >= '".$search['from_date']." 00:00:00' 
					group by amazon_settlement_details.order_id,amazon_settlement_details.seller_account_id 
				) as settlement on t1.seller_fulfillment_order_id=settlement.order_id and t1.seller_account_id = settlement.seller_account_id
				{$where}
			order by displayable_order_date_time desc ";
		return $sql;
	}

}