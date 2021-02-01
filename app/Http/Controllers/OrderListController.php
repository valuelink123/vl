<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OrderListController extends Controller
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
//		if(!Auth::user()->can(['order-list-show'])) die('Permission denied -- order-list-show');
		$data['account'] = $this->getAccountInfo();//得到账号机的信息
		$data['fromDate'] = date('Y-m-d',time()-2*86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
//		$data['fromDate'] = '2021-01-15';//测试日期
		return view('sales/orderIndex',['data'=>$data]);
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
			$data[$key]['status'] = $val['order_status'];
			$data[$key]['date'] = 'Purchase:'.$val['purchase_date'];
			if($val['order_status']=='Canceled'){
				$data[$key]['date'] .= '<br> Canceled:'.$val['last_update_date'];
			}
			$data[$key]['currency'] = $val['currency_code'];
			$data[$key]['tracking_no'] = '/NA';
			$data[$key]['carry_code'] = '/NA';
			$fulfillmentChannel = '';
			//当为AFN的时候为FBA发货，当为MFN的时候为FBM发货
			if($val['fulfillment_channel']=='AFN'){
				$fulfillmentChannel = 'FBA';
			}
			if($val['fulfillment_channel']=='MFN'){
				$fulfillmentChannel = 'FBM';
			}
			$data[$key]['fulfillment_channel'] = $fulfillmentChannel;
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	 * 导出功能
	 */
	public function export()
	{
//		if(!Auth::user()->can(['order-list-export'])) die('Permission denied -- order-list-export');
		$sql = $this->getSql($_GET);
		$data = DB::connection('amazon')->select($sql);
		$data = json_decode(json_encode($data),true);

		$arrayData = array();
		$headArray[] = 'ID';
		$headArray[] = 'Account';
		$headArray[] = 'Amazon Order ID';
		$headArray[] = 'Status';
		$headArray[] = 'Date';
		$headArray[] = 'Asin';
		$headArray[] = 'Seller Skus';
		$headArray[] = 'Amounts';
		$headArray[] = 'Currency';
		$headArray[] = 'Tracking No';
		$headArray[] = 'Carrier Code';
		$headArray[] = 'Settlement ID';
		$headArray[] = 'Settlement Date';
		$headArray[] = 'Fulfillment Channel';
		$headArray[] = 'Posted Date';
		$arrayData[] = $headArray;

		$accounts = $this->getAccountInfo();//得到账号机的信息
		foreach ($data as $key=>$val){
			$val['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			$val['date'] = 'Purchase Date '.$val['purchase_date'];
			$val['date'] = 'Purchase:'.$val['purchase_date'];
			if($val['order_status']=='Canceled'){
				$val['date'] .= '; Canceled:'.$val['last_update_date'];
			}
			//当为AFN的时候为FBA发货，当为MFN的时候为FBM发货
			if($val['fulfillment_channel']=='AFN'){
				$val['fulfillment_channel'] = 'FBA';
			}
			if($val['fulfillment_channel']=='MFN'){
				$val['fulfillment_channel'] = 'FBM';
			}
			$arrayData[] = array(
				$val['id'],
				$val['account'],
				$val['amazon_order_id'],
				$val['order_status'],
				$val['date'],
				$val['asins'],
				$val['seller_skus'],
				$val['amount'],
				$val['currency_code'],
				'/NA',//Tracking No
				'/NA',//Carrier Code
				$val['settlement_id'],
				$val['settlement_date'],
				$val['fulfillment_channel'],
				$val['posted_date'],//发货时间
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
			header('Content-Disposition: attachment;filename="Export_Order_List.xlsx"');//告诉浏览器输出浏览器名称
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
		$where = " where purchase_date >= '".$search['from_date']." 00:00:00' and purchase_date <= '".$search['to_date']." 23:59:59'";
		if(isset($search['account']) && $search['account']){
			$where.= " and orders.seller_account_id in (".$search['account'].")";
		}
		if(isset($search['status']) && $search['status']){
			$where.= " and order_status = '".$search['status']."'";
		}
		if(isset($search['amazon_order_id']) && $search['amazon_order_id']){
			$where.= " and orders.amazon_order_id = '".$search['amazon_order_id']."'";
		}
		if(isset($search['asin']) && $search['asin']){
			$where.= " and orders.asins like '%".$search['asin']."%'";
		}
		if(isset($search['carry_code']) && $search['carry_code']){
			$where.= " and orders.currency_code = '".$search['carry_code']."'";
		}
		if(isset($search['settlement_id']) && $search['settlement_id']){
			$where.= " and settlement_id = '".$search['settlement_id']."'";
		}
		if(isset($search['settlement_date']) && $search['settlement_date']){
			$where.= " and settlement_date >= '".$search['settlement_date']." 00:00:00 ' and settlement_date<= '".$search['settlement_date']." 23:59:59'";
		}
		if(isset($search['fulfillment_channel']) && $search['fulfillment_channel']){
			$where.= " and fulfillment_channel = '".$search['fulfillment_channel']."'";
		}

		$sql = "select SQL_CALC_FOUND_ROWS orders.id,orders.seller_account_id,orders.amazon_order_id,order_status,purchase_date,asins,currency_code,amount,fulfillment_channel,CONCAT(orders.seller_account_id,'_',orders.amazon_order_id) as accountid_orderid,settlement_id,settlement_date,last_update_date,posted_date,orders.seller_skus as seller_skus   
				from orders 
				left join (
					select amazon_settlement_details.seller_account_id as seller_account_id,amazon_settlement_details.order_id as order_id,any_value(amazon_settlements.settlement_id) as settlement_id,any_value(amazon_settlements.deposit_date) as settlement_date 
					from amazon_settlement_details 
					left join amazon_settlements on amazon_settlement_details.settlement_id = amazon_settlements.settlement_id 
					where transaction_type = 'Order' and amazon_settlements.deposit_date >= '{$search['from_date']} 00:00:00'
					group by amazon_settlement_details.seller_account_id,amazon_settlement_details.order_id 
				) as settlement on orders.seller_account_id=settlement.seller_account_id and  orders.amazon_order_id=settlement.order_id 
				left join (
		            select seller_account_id,amazon_order_id,any_value(posted_date) as posted_date 
		            from finances_shipment_events 
		            where posted_date >= '{$search['from_date']} 00:00:00'
		            group by seller_account_id,amazon_order_id 
				) as shipment on orders.seller_account_id=shipment.seller_account_id and  orders.amazon_order_id=shipment.amazon_order_id  
				 {$where} 
				order by purchase_date desc";
		return $sql;
	}

}