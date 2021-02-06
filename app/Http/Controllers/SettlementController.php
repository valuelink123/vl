<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SettlementController extends Controller
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
		$data['account'] = $this->getAccountInfo();//得到账号机的信息
		$data['fromDate'] = date('Y-m-d',time()-7*86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
		return view('financy/settlementIndex',['data'=>$data]);
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
			$settlement_id = $val['settlement_id'];
			$data[$key]['detail'] = '<a href="/settlement/detail?settlement_id='.$settlement_id.'" class="btn btn-success btn-xs" target="_blank">View</a>';
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	 * 导出功能
	 */
	public function export()
	{
		$sql = $this->getSql($_GET);
		$data = DB::connection('amazon')->select($sql);
		$data = json_decode(json_encode($data),true);

		$arrayData = array();
		$headArray[] = 'ID';
		$headArray[] = 'Account';
		$headArray[] = 'Settlement ID';
		$headArray[] = 'Start Date';
		$headArray[] = 'End Date';
		$headArray[] = 'Deposit Date';
		$headArray[] = 'Amount';
		$headArray[] = 'Currency';
		$arrayData[] = $headArray;

		$accounts = $this->getAccountInfo();//得到账号机的信息
		foreach($data as $key=>$val) {
			$val['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			$arrayData[] = array(
				$val['id'],
				$val['account'],
				$val['settlement_id'],
				$val['settlement_start_date'],
				$val['settlement_end_date'],
				$val['deposit_date'],
				$val['total_amount'],
				$val['currency'],
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
			header('Content-Disposition: attachment;filename="Export_Settlement_List.xlsx"');//告诉浏览器输出浏览器名称
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
		$where = " where deposit_date >= '".$search['from_date']." 00:00:00' and deposit_date <= '".$search['to_date']." 23:59:59'";
		if(isset($search['account']) && $search['account']){
			$where.= " and seller_account_id in (".$search['account'].")";
		}
		if(isset($search['currency']) && $search['currency']){
			$where.= " and currency = '".$search['currency']."'";
		}

		if(isset($search['settlement_id']) && $search['settlement_id']){
			$where.= " and settlement_id = '".$search['settlement_id']."'";
		}

		$sql = "select SQL_CALC_FOUND_ROWS id,seller_account_id,settlement_id,settlement_start_date,settlement_end_date,deposit_date,total_amount,currency 
			from amazon_settlements 
			{$where}
			order by deposit_date desc ";
		return $sql;
	}

	//=======================结算明细================================
	/*
	 * 结算明细，根据settlementID去amazon_settlement_details表中找结算明细
	 */
	public function detail()
	{
		$settlement_id = isset($_GET['settlement_id']) && $_GET['settlement_id'] ? $_GET['settlement_id'] : 0;
		$settlementInfo = DB::connection('amazon')->table('amazon_settlements')->where('settlement_id',$settlement_id)->first();
		$data['account'] = $this->getAccountInfo();//得到账号机的信息
		$data['settlementInfo'] = $settlementInfo ? (array)$settlementInfo : array('seller_account_id'=>'','currency'=>'','settlement_id'=>'');
		return view('financy/settlementDetail',['data'=>$data]);
	}

	public function detailList(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$sql = $this->getDetailSql($search);

		if($req['length'] != '-1'){//等于-1时为查看全部的数据
			$limit = $this->dtLimit($req);
			$sql .= " LIMIT {$limit} ";
		}
		$datas = DB::connection('amazon')->select($sql);
		$data = json_decode(json_encode($datas),true);
		$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
		$accounts = $this->getAccountInfo();//得到账号机的信息
		$userInfo = current(getUsers('sap_seller'));
		foreach($data as $key=>$val) {
			$data[$key]['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			$data[$key]['seller'] = '';
			if($val['sap_seller_id']!=NULL){
				$data[$key]['seller'] = isset($userInfo[$val['sap_seller_id']]) ? $userInfo[$val['sap_seller_id']] : '';
			}
			//当为AFN的时候为FBA发货，当为MFN的时候为FBM发货
			$fulfillment = '';
			if($val['fulfillment_id']=='AFN'){
				$fulfillment = 'FBA';
			}
			if($val['fulfillment_id']=='MFN'){
				$fulfillment = 'FBM';
			}
			$data[$key]['fulfillment'] = $fulfillment;
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}
	 public function detailExport()
	 {
		 $sql = $this->getDetailSql($_GET);
		 $data = DB::connection('amazon')->select($sql);
		 $data = json_decode(json_encode($data),true);

		 $arrayData = array();
		 $headArray[] = 'ID';
		 $headArray[] = 'Account';
		 $headArray[] = 'Settlement ID';
		 $headArray[] = 'Transaction Type';
		 $headArray[] = 'Amazon Order ID';
		 $headArray[] = 'Merchant Order ID';
		 $headArray[] = 'Fulfillment';
		 $headArray[] = 'Seller SKU';
		 $headArray[] = 'Shipping Fee';
		 $headArray[] = 'Other Fee';
		 $headArray[] = 'Price Type';
		 $headArray[] = 'Price';
		 $headArray[] = 'Item Related Fee Type';
		 $headArray[] = 'Item Related Fee';
		 $headArray[] = 'Misc Fee';
		 $headArray[] = 'Promotion Fee';
		 $headArray[] = 'BG';
		 $headArray[] = 'BU';
		 $headArray[] = 'Seller';
		 $arrayData[] = $headArray;

		 $accounts = $this->getAccountInfo();//得到账号机的信息
		 $userInfo = current(getUsers('sap_seller'));
		 foreach($data as $key=>$val) {
			 $val['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			 $val['seller'] = '';
			 if($val['sap_seller_id']!=NULL){
				 $val['seller'] = isset($userInfo[$val['sap_seller_id']]) ? $userInfo[$val['sap_seller_id']] : '';
			 }
			 //当为AFN的时候为FBA发货，当为MFN的时候为FBM发货
			 $fulfillment = '';
			 if($val['fulfillment_id']=='AFN'){
				 $fulfillment = 'FBA';
			 }
			 if($val['fulfillment_id']=='MFN'){
				 $fulfillment = 'FBM';
			 }
			 $val['fulfillment'] = $fulfillment;

			 $arrayData[] = array(
				 $val['id'],
				 $val['account'],
				 $val['settlement_id'],
				 $val['transaction_type'],
				 $val['order_id'],
				 $val['merchant_order_id'],
				 $val['fulfillment'],
				 $val['seller_sku'],
				 $val['shipment_fee_amount'],

				 $val['other_fee_amount'],
				 $val['price_type'],
				 $val['price_amount'],
				 $val['item_related_fee_type'],
				 $val['item_related_fee_amount'],
				 $val['misc_fee_amount'],
				 $val['promotion_amount'],
				 $val['bg'],
				 $val['bu'],
				 $val['seller'],
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
			 header('Content-Disposition: attachment;filename="Export_SettlementDetail_List.xlsx"');//告诉浏览器输出浏览器名称
			 header('Cache-Control: max-age=0');//禁止缓存
			 $writer = new Xlsx($spreadsheet);
			 $writer->save('php://output');
		 }
		 die();
	 }

	//获得搜索条件并且返回对应的sql语句
	public function getDetailSql($search)
	{
		$where =" where 1 = 1";
		if($search['from_date'] || $search['to_date']){
			$datewhere = " where 1 = 1";
			if($search['from_date']){
				$datewhere.= " and purchase_date >='".$search['from_date']." 00:00:00'";
			}
			if($search['to_date']){
				$datewhere.= " and purchase_date <='".$search['to_date']." 23:59:59'";
			}
			$where .= " and CONCAT(seller_account_id,'_',order_id) in (select CONCAT(seller_account_id,'_',amazon_order_id) from orders ".$datewhere.")";
		}

		if(isset($search['account']) && $search['account']){
			$where.= " and seller_account_id in (".$search['account'].")";
		}
		if(isset($search['currency']) && $search['currency']){
			$where.= " and currency = '".$search['currency']."'";
		}

		if(isset($search['settlement_id']) && $search['settlement_id']){
			$where.= " and settlement_id = '".$search['settlement_id']."'";
		}
		if(isset($search['seller_sku']) && $search['seller_sku']){
			$where.= " and amazon_settlement_details.seller_sku = '".$search['seller_sku']."'";
		}
		if(isset($search['amazon_order_id']) && $search['amazon_order_id']){
			$where.= " and order_id = '".$search['amazon_order_id']."'";
		}

		$sql="SELECT SQL_CALC_FOUND_ROWS amazon_settlement_details.*,asin,seller_id,sap_seller_id,sap_seller_bg as bg,sap_seller_bu as bu  
				FROM (
					SELECT
					  seller_accounts.label AS account,seller_accounts.mws_seller_id, 
					  CASE amazon_settlement_details.marketplace_name
					  WHEN 'Amazon.com' THEN 'ATVPDKIKX0DER'
					  WHEN 'Amazon.com.mx' THEN 'A1AM78C64UM0Y8'
					  WHEN 'Amazon.ca' THEN 'A2EUQ1WTGCTBG2'
					  WHEN 'Amazon.co.jp' THEN 'A1VC38T7YXB528'
					  WHEN 'Amazon.co.uk' THEN 'A1F83G8C2ARO7P'
					  WHEN 'SI UK Prod Marketplace' THEN 'A1F83G8C2ARO7P'
					  WHEN 'Amazon.de' THEN 'A1PA6795UKMFR9'
					  WHEN 'Amazon.fr' THEN 'A13V1IB3VIYZZH'
					  WHEN 'Amazon.es' THEN 'A1RKKUPIHCS9HS'
					  WHEN 'Amazon.nl' THEN 'A1805IZSGTT6HS'
					  WHEN 'SI Prod IT Marketplace' THEN 'APJ6JRA9NG5V4'
					  ELSE
						seller_accounts.mws_marketplaceid
					END AS marketplace,
						amazon_settlement_details.id as id,seller_account_id,settlement_id,currency,transaction_type,order_id,merchant_order_id,fulfillment_id,amazon_settlement_details.sku as seller_sku,shipment_fee_amount,other_fee_amount,price_amount,item_related_fee_amount,misc_fee_amount,promotion_amount,price_type,item_related_fee_type,posted_date 
					FROM amazon_settlement_details
					LEFT JOIN  seller_accounts ON (amazon_settlement_details.seller_account_id = seller_accounts.id AND seller_accounts.`primary` = 1 )
				) AS amazon_settlement_details 
				left join sap_asin_match_sku on seller_id=mws_seller_id and marketplace_id = marketplace and sap_asin_match_sku.seller_sku=amazon_settlement_details.seller_sku
			  {$where}
			order by posted_date desc";
		return $sql;
	}



}