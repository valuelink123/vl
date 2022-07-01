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

	public $reasonSummaryType = [
		0=>['name'=>'品质问题退货数量','reason'=>['DEFECTIVE','QUALITY_UNACCEPTABLE','APPAREL_STYLE','MISSING_PARTS','DAMAGED_BY_CARRIER','DAMAGED_BY_FC','CUSTOMER_DAMAGED']],
		1=>['name'=>'非品质问题退货数量','reason'=>['DID_NOT_LIKE_FABRIC','PRODUCT_NOT_ITALIAN','SWITCHEROO','UNWANTED_ITEM','NOT_AS_DESCRIBED','ORDERED_WRONG_ITEM','MISORDERED','UNDELIVERABLE_UNCLAIMED','UNDELIVERABLE_UNKNOWN','NEVER_ARRIVED','UNDELIVERABLE_CARRIER_MISS_SORTED','UNDELIVERABLE_INSUFFICIENT_ADDRESS','UNDELIVERABLE_MISSING_LABEL','UNDELIVERABLE_FAILED_DELIVERY_ATTEMPTS','UNDELIVERABLE_REFUSED','FOUND_BETTER_PRICE','MISSED_ESTIMATED_DELIVERY','UNAUTHORIZED_PURCHASE','NOT_COMPATIBLE','APPAREL_TOO_LARGE','APPAREL_TOO_SMALL','PART_NOT_COMPATIBLE','NO_REASON_GIVEN','EXTRA_ITEM','EXCESSIVE_INSTALLATION','CARRIER_DAMAGED','DAMAGED','SELLABLE']],
	];

	public $ccpAdmin = array("huangshan@valuelinkltd.com","lidan@valuelinkcorp.com","wuweiye@valuelinkcorp.com","luodenglin@valuelinkcorp.com","zhouzhiwen@valuelinkltd.com","zhangjianqun@valuelinkcorp.com","sunhanshan@valuelinkcorp.com","wangxiaohua@valuelinkltd.com","zhoulinlin@valuelinkcorp.com","wangshuang@valuelinkltd.com","lizhuojun@valuelinkcorp.com","lixiaojian@valuelinkltd.com","shiqingbo@valuelinkltd.com");

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
	 *
	 * 退货汇总
	 *
	 * */
	public function returnSummaryAnalysis(Request $req)
	{
		if(!Auth::user()->can(['return-summary-analysis'])) die('Permission denied -- return-summary-analysis');
		$reasonSummaryType = $this->reasonSummaryType;
		$case = " ";
		$typestr = '';
		foreach($reasonSummaryType as $key=>$typeArr){
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
				if($_REQUEST['order'][0]['column']==1) $orderby = 'tb.asin';
				if($_REQUEST['order'][0]['column']==2) $orderby = 'tb.sellersku';
				if($_REQUEST['order'][0]['column']==3) $orderby = 'type_0';
				if($_REQUEST['order'][0]['column']==4) $orderby = 'type_1';
				$sort = $_REQUEST['order'][0]['dir'];
			}
			//搜索条件如下：from_date,to_date
			$where = " where return_date >= '".$search['from_date']." 00:00:00' and return_date <= '".$search['to_date']." 23:59:59'";
			//$where = " where return_date >= '2020-04-24 00:00:00' and return_date <= '2020-04-25 23:59:59'";
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
			$sql = "select SQL_CALC_FOUND_ROWS tb.sku,any_value(tb.asin) as asin,any_value(tb.seller_sku) as seller_sku,sum(type_0) as type_0,sum(type_1) as type_1,sum(type_0)/(sum(type_0)+sum(type_1)) as qualityReturnQuantityPercentage
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
			$dataA = json_decode(json_encode($_data),true);

			$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;

			if(!empty($dataA)){

				//$aisnList = "";
				foreach($dataA as $key=>$val){
					//$aisnList .= ",'".$val['sku']."'";
					if($val['sku'] == ''){
						$data[$key]['sku'] = '';
						$data[$key]['asin'] = '';
						$data[$key]['seller_sku'] = '';
						$data[$key]['type_0'] = $val['type_0'];
						$data[$key]['type_1'] = $val['type_1'];
						$data[$key]['qualityReturnQuantityPercentage'] = round($val['qualityReturnQuantityPercentage'], 4)*100 ."%";
						$data[$key]['units'] = 0;
						$data[$key]['percentSales'] = 0;
					}else{
						$sql_units = $this->getSql($search,$val['sku']);
						$_itemData = DB::connection('amazon')->select($sql_units);
						$itemData = json_decode(json_encode($_itemData),true);
						if(!empty($itemData)){
							if($val['sku'] == $itemData[0]['sku']){
								$data[$key]['sku'] = $val['sku'];
								$data[$key]['asin'] = $val['asin'];
								$data[$key]['seller_sku'] = $val['seller_sku'];
								$data[$key]['type_0'] = $val['type_0'];
								$data[$key]['type_1'] = $val['type_1'];
								$data[$key]['qualityReturnQuantityPercentage'] = round($val['qualityReturnQuantityPercentage'], 4)*100 ."%";
								$data[$key]['units'] = $itemData[0]['units'];
								$data[$key]['percentSales'] = round(($val['type_0']/$itemData[0]['units']) ,4)*100 ."%";
							}else{
								$data[$key]['sku'] = $val['sku'];
								$data[$key]['asin'] = $val['asin'];
								$data[$key]['seller_sku'] = $val['seller_sku'];
								$data[$key]['type_0'] = $val['type_0'];
								$data[$key]['type_1'] = $val['type_1'];
								$data[$key]['qualityReturnQuantityPercentage'] = round($val['qualityReturnQuantityPercentage'], 4)*100 ."%";
								$data[$key]['units'] = 0;
								$data[$key]['percentSales'] = 0;
							}
						}else{
							$data[$key]['sku'] = $val['sku'];
							$data[$key]['asin'] = $val['asin'];
							$data[$key]['seller_sku'] = $val['seller_sku'];
							$data[$key]['type_0'] = $val['type_0'];
							$data[$key]['type_1'] = $val['type_1'];
							$data[$key]['qualityReturnQuantityPercentage'] = round($val['qualityReturnQuantityPercentage'], 4)*100 ."%";
							$data[$key]['units'] = 0;
							$data[$key]['percentSales'] = 0;
						}
					}

				}
				$data = array_values($data);
			}else{
				$data['sku'] = '';
				$data['asin'] = '';
				$data['seller_sku'] = '';
				$data['type_0'] = '';
				$data['type_1'] = '';
				$data['qualityReturnQuantityPercentage'] = '';
				$data['units'] = '';
				$data['percentSales'] = '';
			}

			return compact('data', 'recordsTotal', 'recordsFiltered');
		}

		$data['fromDate'] = date('Y-m-d', time() - 2 * 86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
//		$data['fromDate'] = '2021-01-15';//测试日期
		$data['reasonType'] = $reasonSummaryType;

		return view('analysis/return_summary', ['data' => $data]);
	}

	//得到sql查询语句
	public function getSql($search,$sku)
	{
//		$site = isset($search['site']) ? $search['site'] : '';//站点，为marketplaceid
//		$account = isset($search['account']) ? $search['account'] : '';//账号id,例如115,137
//		$bg = isset($search['bg']) ? $search['bg'] : '';
//		$bu = isset($search['bu']) ? $search['bu'] : '';
//		$asin = isset($search['asin']) ? trim($search['asin'],'+') : '';//asin输入框的值
//		$timeType = isset($search['timeType']) ? $search['timeType'] : '';//时间类型，默认是0为北京时间，1为亚马逊后台当地时间
//		$this->start_date = isset($search['from_date']) ? $search['from_date'] : '';
//		$this->end_date = isset($search['end_date']) ? $search['end_date'] : '';
		$where = $orderwhere = " and purchase_date BETWEEN STR_TO_DATE( '".$search['from_date']." 00:00:00', '%Y-%m-%d %H:%i:%s' ) AND STR_TO_DATE('".$search['to_date']." 23:59:59', '%Y-%m-%d %H:%i:%s' )";
		//$where = $orderwhere = " and purchase_date BETWEEN STR_TO_DATE( '2020-04-24 00:00:00', '%Y-%m-%d %H:%i:%s' ) AND STR_TO_DATE('2020-04-25 23:59:59', '%Y-%m-%d %H:%i:%s' )";
		//$account搜索两个表的字段都为seller_account_id
//		if($account){
//			$where = $orderwhere .= ' and order_items.seller_account_id in('.$account.')';
//		}
//		$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
//		$orderwhere .= " and sales_channel = '".ucfirst($domain)."'";
//		//用户权限sap_asin_match_sku
//		$userwhere = $this->getUserWhere($site,$bg,$bu);
//		if($asin){
//			$where .= " and order_items.asin = '".$asin."'";
//		}
		$sql ="SELECT SQL_CALC_FOUND_ROWS sku,SUM( quantity_ordered ) AS units
			FROM
			  (SELECT order_items.quantity_ordered,order_items.seller_sku,sap_asin_match_sku.sku as sku
			  	FROM order_items
			  	LEFT JOIN sap_asin_match_sku ON (order_items.seller_sku=sap_asin_match_sku.seller_sku) WHERE order_items.amazon_order_id IN
				  (
					SELECT amazon_order_id
					FROM orders
					WHERE order_status IN ( 'PendingAvailability', 'Pending', 'Unshipped', 'PartiallyShipped', 'Shipped', 'InvoiceUnconfirmed', 'Unfulfillab' ) {$orderwhere}
				  )
			  	{$where}
			  	and order_items.asin in( SELECT ASIN FROM sap_asin_match_sku WHERE sku IN ('".$sku."') )
			) AS kk GROUP BY sku order by units desc";
		return $sql;
	}

	//得到搜索时间的sql
	public function getDateWhere($site,$timeType)
	{
		$dateRange = $this->getDateRange();
		$startDate = $dateRange['startDate'];
		$endDate = $dateRange['endDate'];
		$date_field = 'purchase_date';
		$dateconfig = array('A1PA6795UKMFR9','A1RKKUPIHCS9HS','A13V1IB3VIYZZH','APJ6JRA9NG5V4');//utc+2:00
		if($timeType==1){//选的是后台当地时间
			if($site=='A1VC38T7YXB528'){//日本站点，date字段+9hour
				$date_field = 'date_add(purchase_date,INTERVAL 9 hour) ';
			}elseif($site=='A1F83G8C2ARO7P'){//英国站点+1小时，uTc+1:00
				$date_field = 'date_add(purchase_date,INTERVAL 1 hour) ';
			}elseif(in_array($site,$dateconfig)){//站点+2小时，utc+2:00
				$date_field = 'date_add(purchase_date,INTERVAL 2 hour) ';
			}else{//其他站点，date字段-7hour
				$date_field = 'date_sub(purchase_date,INTERVAL 7 hour) ';
			}
		}else{//北京时间加上8小时
			$date_field = 'date_add(purchase_date,INTERVAL 8 hour) ';
		}
		$where = " and {$date_field} BETWEEN STR_TO_DATE( '".$startDate."', '%Y-%m-%d %H:%i:%s' ) AND STR_TO_DATE('".$endDate."', '%Y-%m-%d %H:%i:%s' )";
		return $where;
	}

	//得到用户的权限数据查询语句，根据sap_asin_match_sku去查数据
	public function getUserWhere()
	{
		$userdata = Auth::user();
//		$userWhere = " where marketplace_id  = '".$site."'";
		$userWhere = " where 1=1";
		if (!in_array(Auth::user()->email, $this->ccpAdmin)) {
			if ($userdata->seller_rules) {
				$rules = explode("-", $userdata->seller_rules);
				if (array_get($rules, 0) != '*') $userWhere .= " and sap_seller_bg = '".array_get($rules, 0)."'";
				if (array_get($rules, 1) != '*') $userWhere .= " and sap_seller_bu = '".array_get($rules, 1)."'";
			}elseif($userdata->sap_seller_id){
				$userWhere .= " and sap_seller_id = ".$userdata->sap_seller_id;
			}
		}

//		if($bg){
//			$userWhere .= " and sap_seller_bg = '".$bg."'";
//		}
//		if($bu){
//			$userWhere .= " and sap_seller_bu = '".$bu."'";
//		}
		$userWhere = "select DISTINCT sap_asin_match_sku.asin from sap_asin_match_sku {$userWhere}
					UNION ALL
					select DISTINCT asin_match_relation.asin from asin_match_relation {$userWhere}";
		return $userWhere;
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