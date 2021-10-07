<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReturnAnalysisController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */
	use \App\Traits\DataTables;
	use \App\Traits\Mysqli;

	public $reasonType = [
		0=>['name'=>'其他','reason'=>['DID_NOT_LIKE_FABRIC','PRODUCT_NOT_ITALIAN']],
		1=>['name'=>'产品缺陷','reason'=>['DEFECTIVE']],
		2=>['name'=>'品质问题','reason'=>['QUALITY_UNACCEPTABLE','APPAREL_STYLE']],
		3=>['name'=>'产品损坏','reason'=>['DAMAGED_BY_CARRIER','DAMAGED_BY_FC','CUSTOMER_DAMAGED']],
		4=>['name'=>'缺少配件','reason'=>['MISSING_PARTS']],
		5=>['name'=>'不想要了','reason'=>['SWITCHEROO','UNWANTED_ITEM']],
		6=>['name'=>'和描述不符','reason'=>['NOT_AS_DESCRIBED']],
		7=>['name'=>'下错订单','reason'=>['ORDERED_WRONG_ITEM','MISORDERED']],
		8=>['name'=>'未收到货','reason'=>['UNDELIVERABLE_UNCLAIMED','UNDELIVERABLE_UNKNOWN','NEVER_ARRIVED','UNDELIVERABLE_CARRIER_MISS_SORTED','UNDELIVERABLE_INSUFFICIENT_ADDRESS','UNDELIVERABLE_MISSING_LABEL','UNDELIVERABLE_FAILED_DELIVERY_ATTEMPTS','UNDELIVERABLE_REFUSED']],
		9=>['name'=>'有更好价格','reason'=>['FOUND_BETTER_PRICE']],
		10=>['name'=>'交期超时','reason'=>['MISSED_ESTIMATED_DELIVERY']],
		11=>['name'=>'未经授权购买','reason'=>['UNAUTHORIZED_PURCHASE']],
		12=>['name'=>'不适合','reason'=>['NOT_COMPATIBLE','APPAREL_TOO_LARGE','APPAREL_TOO_SMALL','PART_NOT_COMPATIBLE']],
		13=>['name'=>'未知原因','reason'=>['NO_REASON_GIVEN']],
		14=>['name'=>'发错货','reason'=>['EXTRA_ITEM']],
		15=>['name'=>'买多了','reason'=>['EXCESSIVE_INSTALLATION']],
		16=>['name'=>'物流损坏','reason'=>['CARRIER_DAMAGED']],
		17=>['name'=>'损坏','reason'=>['DAMAGED']],
		18=>['name'=>'可再售','reason'=>['SELLABLE']],
	];
	public function __construct()
	{
		$this->middleware('auth');
		parent::__construct();
	}

	/**
	 * Show the application dashboard
	 */
	public function returnAnalysis(Request $req)
	{
		if(!Auth::user()->can(['return-analysis'])) die('Permission denied -- return-analysis');
		$reasonType = $this->reasonType;
		$case = " ";
		$typestr = '';
		foreach($reasonType as $key=>$typeArr){
			$case .= " CASE amazon_returns.reason ";
			foreach($typeArr['reason'] as $type){
				$case.= " WHEN '".$type."' THEN amazon_returns.quantity ";
			}
			$typestr .= "sum(type_{$key}) as type_{$key}";
			$case.= " ELSE 0 END AS type_{$key},";
		}

		if($_POST){
			$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
			$search = $this->getSearchData(explode('&',$search));
			$orderby = 'tb.sku';
			$sort = 'desc';
			if(isset($_REQUEST['order'][0])){
				if($_REQUEST['order'][0]['column']==0) $orderby = 'tb.sku';
				if($_REQUEST['order'][0]['column']==1) $orderby = 'title';
				if($_REQUEST['order'][0]['column']==2) $orderby = 'type_0';
				if($_REQUEST['order'][0]['column']==3) $orderby = 'type_1';
				if($_REQUEST['order'][0]['column']==4) $orderby = 'type_2';
				if($_REQUEST['order'][0]['column']==5) $orderby = 'type_3';
				if($_REQUEST['order'][0]['column']==6) $orderby = 'type_4';
				if($_REQUEST['order'][0]['column']==7) $orderby = 'type_5';
				if($_REQUEST['order'][0]['column']==8) $orderby = 'type_6';
				if($_REQUEST['order'][0]['column']==9) $orderby = 'type_7';
				if($_REQUEST['order'][0]['column']==10) $orderby = 'type_8';
				if($_REQUEST['order'][0]['column']==11) $orderby = 'type_9';
				if($_REQUEST['order'][0]['column']==12) $orderby = 'type_10';
				if($_REQUEST['order'][0]['column']==13) $orderby = 'type_11';
				if($_REQUEST['order'][0]['column']==14) $orderby = 'type_12';
				if($_REQUEST['order'][0]['column']==15) $orderby = 'type_13';
				if($_REQUEST['order'][0]['column']==16) $orderby = 'type_14';
				if($_REQUEST['order'][0]['column']==17) $orderby = 'type_15';
				if($_REQUEST['order'][0]['column']==18) $orderby = 'type_16';
				if($_REQUEST['order'][0]['column']==19) $orderby = 'type_17';
				if($_REQUEST['order'][0]['column']==20) $orderby = 'type_18';
				$sort = $_REQUEST['order'][0]['dir'];
			}
			//搜索条件如下：from_date,to_date
			$where = " where return_date >= '".$search['from_date']." 00:00:00' and return_date <= '".$search['to_date']." 23:59:59'";
			$where_sku = ' where 1 = 1 ';

			if (Auth::user()->seller_rules) {
				$rules = explode("-",Auth::user()->seller_rules);
				if(array_get($rules,0)!='*') $where_sku.= " and tb.sap_seller_bg='".array_get($rules,0)."'";
				if(array_get($rules,1)!='*') $where_sku.= " and tb.sap_seller_bu='".array_get($rules,1)."'";
			} elseif (Auth::user()->sap_seller_id) {
				$where_sku.= " and tb.sap_seller_id=".Auth::user()->sap_seller_id;
			}

			if(isset($search['sku']) && $search['sku']){
				$where_sku.= " and tb.sku = '".$search['sku']."'";
			}
			$sql = "select SQL_CALC_FOUND_ROWS tb.sku,sum(type_0) as type_0,sum(type_1) as type_1,sum(type_2) as type_2,sum(type_3) as type_3,sum(type_4) as type_4,sum(type_5) as type_5,sum(type_6) as type_6,sum(type_7) as type_7,sum(type_8) as type_8,sum(type_9) as type_9,sum(type_10) as type_10,sum(type_11) as type_11,sum(type_12) as type_12,sum(type_13) as type_13,sum(type_14) as type_14,sum(type_15) as type_15,sum(type_16) as type_16,sum(type_17) as type_17,sum(type_18) as type_18,any_value (sap_skus.description) AS title
					from (
					    SELECT seller_accounts.mws_seller_id as mws_seller_id,amazon_returns.seller_account_id,amazon_returns.seller_sku as seller_sku,amazon_returns.asin,{$case} mws_marketplaceid AS mws_marketplaceid
						FROM amazon_returns 
						LEFT JOIN seller_accounts ON amazon_returns.seller_account_id = seller_accounts.id 
						{$where}
					) as ta 
				left join sap_asin_match_sku as tb on (ta.asin=tb.asin and ta.seller_sku=tb.seller_sku and ta.mws_seller_id=tb.seller_id and ta.mws_marketplaceid=tb.marketplace_id) 
				left join sap_skus on tb.sku=sap_skus.sku 
				{$where_sku}
				GROUP BY tb.sku ORDER BY {$orderby} {$sort}";

			if($req['length'] != '-1'){//等于-1时为查看全部的数据
				$limit = $this->dtLimit($req);
				$sql .= " LIMIT {$limit} ";
			}

			$_data = DB::connection('amazon')->select($sql);
			$data = json_decode(json_encode($_data),true);
			$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;

			foreach($data as $key=>$val){
				$data[$key]['title'] = '<span title="'.$val['title'].'">'.$val['title'].'</span>';
				$data[$key]['total'] = 0;
				foreach($reasonType as $tk=>$typeArr){
					$data[$key]['total']  = $data[$key]['total'] + $val['type_'.$tk];
				}
			}
			return compact('data', 'recordsTotal', 'recordsFiltered');
		}
		$data['fromDate'] = date('Y-m-d', time() - 2 * 86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
//		$data['fromDate'] = '2021-01-15';//测试日期
		$data['reasonType'] = $reasonType;
		return view('analysis/return', ['data' => $data]);
	}
	/*
	 * 导出退货原因分析
	 */
	public function export(Request $req)
	{
		if(!Auth::user()->can(['return-analysis-export'])) die('Permission denied -- return-analysis-export');
		$reasonType = $this->reasonType;
		$case = " ";
		$typestr = '';
		foreach($reasonType as $key=>$typeArr){
			$case .= " CASE amazon_returns.reason ";
			foreach($typeArr['reason'] as $type){
				$case.= " WHEN '".$type."' THEN amazon_returns.quantity ";
			}
			$typestr .= "sum(type_{$key}) as type_{$key}";
			$case.= " ELSE 0 END AS type_{$key},";
		}

		$orderby = 'tb.sku';
		$sort = 'desc';

		//搜索条件如下：from_date,to_date
		$where = " where return_date >= '".$req['from_date']." 00:00:00' and return_date <= '".$req['to_date']." 23:59:59'";
		$where_sku = ' where 1 = 1 ';

		if (Auth::user()->seller_rules) {
			$rules = explode("-",Auth::user()->seller_rules);
			if(array_get($rules,0)!='*') $where_sku.= " and tb.sap_seller_bg='".array_get($rules,0)."'";
			if(array_get($rules,1)!='*') $where_sku.= " and tb.sap_seller_bu='".array_get($rules,1)."'";
		} elseif (Auth::user()->sap_seller_id) {
			$where_sku.= " and tb.sap_seller_id=".Auth::user()->sap_seller_id;
		}

		if(isset($req['sku']) && $req['sku']){
			$where_sku.= " and tb.sku = '".$req['sku']."'";
		}
		$sql = "select SQL_CALC_FOUND_ROWS tb.sku,sum(type_0) as type_0,sum(type_1) as type_1,sum(type_2) as type_2,sum(type_3) as type_3,sum(type_4) as type_4,sum(type_5) as type_5,sum(type_6) as type_6,sum(type_7) as type_7,sum(type_8) as type_8,sum(type_9) as type_9,sum(type_10) as type_10,sum(type_11) as type_11,sum(type_12) as type_12,sum(type_13) as type_13,sum(type_14) as type_14,sum(type_15) as type_15,sum(type_16) as type_16,sum(type_17) as type_17,sum(type_18) as type_18,any_value (sap_skus.description) AS title
				from (
					SELECT seller_accounts.mws_seller_id as mws_seller_id,amazon_returns.seller_account_id,amazon_returns.seller_sku as seller_sku,amazon_returns.asin,{$case} mws_marketplaceid AS mws_marketplaceid
					FROM amazon_returns 
					LEFT JOIN seller_accounts ON amazon_returns.seller_account_id = seller_accounts.id 
					{$where}
				) as ta 
			left join sap_asin_match_sku as tb on (ta.asin=tb.asin and ta.seller_sku=tb.seller_sku and ta.mws_seller_id=tb.seller_id and ta.mws_marketplaceid=tb.marketplace_id) 
			left join sap_skus on tb.sku=sap_skus.sku 
			{$where_sku}
			GROUP BY tb.sku ORDER BY {$orderby} {$sort}";

		$_data = DB::connection('amazon')->select($sql);
		$data = json_decode(json_encode($_data),true);
		//表头
		$headArray = array('SKU','Title');
		foreach($reasonType as $tk=>$typeArr){
			$headArray[] = $typeArr['name'];
		}
		$headArray[] = '小计';
		$arrayData[] = $headArray;

		foreach($data as $key=>$val){
			$_data = array($val['sku'],'<span title="'.$val['title'].'">'.$val['title'].'</span>');
			for($i=0;$i<=18;$i++){
				$_data[] = $val['type_'.$i];
			}
			$data[$key]['total'] = 0;
			foreach($reasonType as $tk=>$typeArr){
				$data[$key]['total'] = $data[$key]['total'] + $val['type_'.$tk];
			}
			$_data[] = $data[$key]['total'];
			$arrayData[] = $_data;
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
			header('Content-Disposition: attachment;filename="return_analysis.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}

	//asin维度分析
	public function asinAnalysis(Request $req)
	{

		if(!Auth::user()->can(['asin-analysis'])) die('Permission denied -- asin-analysis');
		if($_POST){
			$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
			$search = $this->getSearchData(explode('&',$search));
			//搜索条件如下：from_date,to_date
			$where_return = " return_date >= '".$search['from_date']." 00:00:00' and return_date <= '".$search['to_date']." 23:59:59'";
			$where_refund = " where posted_date >= '".$search['from_date']." 00:00:00' and posted_date <= '".$search['to_date']." 23:59:59'";

			if(isset($search['account']) && $search['account']){
				$where_refund.= " and t1.current_seller_account_id in (".$search['account'].")";
			}
			//站点权限
			$where_refund .= " and current_marketplace_id = '".ucfirst($search['site'])."'";

			$where = ' where 1 = 1 ';
			if(isset($search['asin']) && $search['asin']){
				$where.= " and tb.asin = '".$search['asin']."'";
			}

			if (Auth::user()->seller_rules) {
				$rules = explode("-",Auth::user()->seller_rules);
				if(array_get($rules,0)!='*') $where.= " and tb.sap_seller_bg='".array_get($rules,0)."'";
				if(array_get($rules,1)!='*') $where.= " and tb.sap_seller_bu='".array_get($rules,1)."'";
			} elseif (Auth::user()->sap_seller_id) {
				$where.= " and tb.sap_seller_id=".Auth::user()->sap_seller_id;
			}


			$orderby = ' return_quantity ';
			$sort = ' desc ';
			if(isset($_REQUEST['order'][0])){
				if($_REQUEST['order'][0]['column']==0) $orderby = 'tb.asin';
				if($_REQUEST['order'][0]['column']==1) $orderby = 'title';
				if($_REQUEST['order'][0]['column']==2) $orderby = 'ta.seller_account_id';
				if($_REQUEST['order'][0]['column']==3) $orderby = 'refund_quantity';
				if($_REQUEST['order'][0]['column']==4) $orderby = 'return_quantity';
				$sort = $_REQUEST['order'][0]['dir'];
			}

			$sql="select SQL_CALC_FOUND_ROWS tb.asin,ta.seller_account_id,sum(quantity_shipped) as refund_quantity,sum(tc.quantity) as return_quantity,any_value(sap_skus.description) as title 
				from (
						select amazon_order_id,current_seller_account_id as seller_account_id,current_marketplace_id as marketplace_id,seller_sku,
						any_value(quantity_shipped) as quantity_shipped,any_value(mws_seller_id) as mws_seller_id
						from finances_refund_events as t1 
						left join seller_accounts as t2 on t1.current_seller_account_id=t2.id 
						{$where_refund}
						group by amazon_order_id,seller_sku,current_seller_account_id,current_marketplace_id
				) as ta 
				left join sap_asin_match_sku as tb on (ta.seller_sku=tb.seller_sku and ta.mws_seller_id=tb.seller_id and ta.marketplace_id=tb.marketplace_id)
				left join amazon_returns as tc on  tb.asin=tc.asin and ta.seller_account_id=tc.seller_account_id and ta.amazon_order_id = tc.amazon_order_id 
				left join sap_skus on tb.sku=sap_skus.sku 
				{$where}
				group by tb.asin,ta.seller_account_id order by {$orderby} {$sort}";

			if($req['length'] != '-1'){//等于-1时为查看全部的数据
				$limit = $this->dtLimit($req);
				$sql .= " LIMIT {$limit} ";
			}

			$datas = DB::connection('amazon')->select($sql);
			$data = json_decode(json_encode($datas),true);
			$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
			$accounts = $this->getAccountInfo();//得到账号机的信息
			foreach($data as $key=>$val) {
				$data[$key]['title'] = '<span title="'.$val['title'].'">'.$val['title'].'</span>';
				$data[$key]['refund_quantity'] = $val['refund_quantity']>0 ? $val['refund_quantity'] : 0;
				$data[$key]['return_quantity'] = $val['return_quantity']>0 ? $val['return_quantity'] : 0;
				$data[$key]['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			}
			return compact('data', 'recordsTotal', 'recordsFiltered');
		}
		$data['account'] = $this->getAccountInfo();//得到账号机的信息
		$data['fromDate'] = date('Y-m-d', time() - 2 * 86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
		return view('analysis/asin', ['data' => $data]);
	}

	//sku维度分析
	public function skuAnalysis(Request $req)
	{
		if(!Auth::user()->can(['sku-analysis'])) die('Permission denied -- sku-analysis');
		if($_POST){
			$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
			$search = $this->getSearchData(explode('&',$search));
			//搜索条件如下：from_date,to_date
			$where_return = " where return_date >= '".$search['from_date']." 00:00:00' and return_date <= '".$search['to_date']." 23:59:59'";
			$where_refund = " where posted_date >= '".$search['from_date']." 00:00:00' and posted_date <= '".$search['to_date']." 23:59:59'";

			if(isset($search['account']) && $search['account']){
				$where_refund.= " and t1.current_seller_account_id in (".$search['account'].")";
			}

			$where = ' where 1 = 1 ';
			if(isset($search['sku']) && $search['sku']){
				$where.= " and tb.sku = '".$search['sku']."'";
			}

			if (Auth::user()->seller_rules) {
				$rules = explode("-",Auth::user()->seller_rules);
				if(array_get($rules,0)!='*') $where.= " and tb.sap_seller_bg='".array_get($rules,0)."'";
				if(array_get($rules,1)!='*') $where.= " and tb.sap_seller_bu='".array_get($rules,1)."'";
			} elseif (Auth::user()->sap_seller_id) {
				$where.= " and tb.sap_seller_id=".Auth::user()->sap_seller_id;
			}
			
			$orderby = ' return_quantity ';
			$sort = ' desc ';
			if(isset($_REQUEST['order'][0])){
				if($_REQUEST['order'][0]['column']==0) $orderby = 'tb.sku';
				if($_REQUEST['order'][0]['column']==1) $orderby = 'title';
				if($_REQUEST['order'][0]['column']==2) $orderby = 'refund_quantity';
				if($_REQUEST['order'][0]['column']==3) $orderby = 'return_quantity';
				$sort = $_REQUEST['order'][0]['dir'];
			}

			$sql="select SQL_CALC_FOUND_ROWS tb.sku,sum(quantity_shipped) as refund_quantity,sum(tc.quantity) as return_quantity,any_value(sap_skus.description) as title  
				from (
						select amazon_order_id,current_seller_account_id as seller_account_id,current_marketplace_id as marketplace_id,seller_sku,
						any_value(quantity_shipped) as quantity_shipped,any_value(mws_seller_id) as mws_seller_id
						from finances_refund_events as t1 
						left join seller_accounts as t2 on t1.current_seller_account_id=t2.id 
						{$where_refund}
						group by amazon_order_id,seller_sku,current_seller_account_id,current_marketplace_id
				) as ta 
				left join sap_asin_match_sku as tb on (ta.seller_sku=tb.seller_sku and ta.mws_seller_id=tb.seller_id and ta.marketplace_id=tb.marketplace_id)
				left join amazon_returns as tc on  tb.asin=tc.asin and ta.seller_account_id=tc.seller_account_id and ta.amazon_order_id = tc.amazon_order_id 
				left join sap_skus on tb.sku=sap_skus.sku 
				{$where}
				group by tb.sku order by {$orderby} {$sort}";

			if($req['length'] != '-1'){//等于-1时为查看全部的数据
				$limit = $this->dtLimit($req);
				$sql .= " LIMIT {$limit} ";
			}

			$datas = DB::connection('amazon')->select($sql);
			$data = json_decode(json_encode($datas),true);
			$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
			foreach($data as $key=>$val) {
				$data[$key]['title'] = '<span title="'.$val['title'].'">'.$val['title'].'</span>';
				$data[$key]['return_quantity'] = $val['return_quantity']>0 ? $val['return_quantity'] : 0;
				$data[$key]['refund_quantity'] = $val['refund_quantity']>0 ? $val['refund_quantity'] : 0;

			}
			return compact('data', 'recordsTotal', 'recordsFiltered');
		}
		$data['account'] = $this->getAccountInfo();//得到账号机的信息
		$data['fromDate'] = date('Y-m-d', time() - 2 * 86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
		return view('analysis/sku', ['data' => $data]);
	}

	//通过sku得到sku的标题
//	public function getSkuInfoBySku($skus)
//	{
//		$_skuData = DB::connection('amazon')->table('sap_skus')->select("sku","description")->whereIn('sku',$skus)->get();
//		$skuData = array();
//		foreach($_skuData as $sk=>$sv){
//			$skuData[$sv->sku] = $sv->description;
//		}
//		return $skuData;
//	}
}