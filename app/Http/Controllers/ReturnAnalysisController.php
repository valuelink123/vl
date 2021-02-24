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
		$reasonType = [
			1=>['name'=>'产品缺陷','reason'=>['DEFECTIVE']],
			2=>['name'=>'品质问题','reason'=>['QUALITY_UNACCEPTABLE','APPAREL_STYLE']],
			3=>['name'=>'产品损坏','reason'=>['DAMAGED_BY_CARRIER','DAMAGED_BY_FC','CUSTOMER_DAMAGED']],
			4=>['name'=>'缺少配件','reason'=>['MISSING_PARTS']],
			5=>['name'=>'不想要了','reason'=>['SWITCHEROO','UNWANTED_ITEM']],
			6=>['name'=>'和描述不相符','reason'=>['NOT_AS_DESCRIBED']],
			7=>['name'=>'下错订单','reason'=>['ORDERED_WRONG_ITEM','MISORDERED']],
			8=>['name'=>'未收到货','reason'=>['UNDELIVERABLE_UNCLAIMED','UNDELIVERABLE_UNKNOWN','NEVER_ARRIVED','UNDELIVERABLE_CARRIER_MISS_SORTED','UNDELIVERABLE_INSUFFICIENT_ADDRESS','UNDELIVERABLE_MISSING_LABEL','UNDELIVERABLE_FAILED_DELIVERY_ATTEMPTS','UNDELIVERABLE_REFUSED']],
			9=>['name'=>'有更好的价格','reason'=>['FOUND_BETTER_PRICE']],
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
		$case = " CASE reason ";
		foreach($reasonType as $key=>$typeArr){
			foreach($typeArr['reason'] as $type){
				$case.= " WHEN '".$type."' THEN 'type_".$key."' ";
			}
		}
		$case .= " ELSE 'type_0' END AS type";
		if($_POST){
			$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
			$search = $this->getSearchData(explode('&',$search));
			//搜索条件如下：from_date,to_date
			$where = " where return_date >= '".$search['from_date']." 00:00:00' and return_date <= '".$search['to_date']." 23:59:59'";
			$where_sku = ' where 1 = 1 ';
			if(isset($search['sku']) && $search['sku']){
				$where_sku.= " and tb.sku = '".$search['sku']."'";
			}
			$sql = "select SQL_CALC_FOUND_ROWS type,tb.sku,sum(quantity) as quantity,any_value(sap_skus.description) as title 
				from(
					select t1.seller_sku,t1.asin,reason,`status`,detailed_disposition as `condition`,mws_seller_id,mws_marketplaceid,quantity,$case
					from amazon_returns as t1
					left join seller_accounts as t2 on t1.seller_account_id=t2.id
				    {$where}
				) as ta
				left join sap_asin_match_sku as tb on (ta.asin=tb.asin and ta.seller_sku=tb.seller_sku and ta.mws_seller_id=tb.seller_id and ta.mws_marketplaceid=tb.marketplace_id) 
				left join sap_skus on tb.sku=sap_skus.sku 
				{$where_sku}
				group by type,tb.sku order by tb.sku desc";

			$_data = DB::connection('amazon')->select($sql);
			$_data = json_decode(json_encode($_data),true);
//			$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;


			$datas = array();
			foreach($_data as $key=>$val) {
				if(isset($datas[$val['sku']]['total'])){
					$datas[$val['sku']]['total'] = $datas[$val['sku']]['total']+$val['quantity'];
					$datas[$val['sku']][$val['type']] = $val['quantity'];
				}else{
					//数据初始化
					$datas[$val['sku']]['sku'] = $val['sku'];
					$datas[$val['sku']]['title'] = $val['title'];
					$datas[$val['sku']]['total'] = $val['quantity'];
					foreach($reasonType as $key=>$typeArr){
						if($val['type']=='type_'.$key){
							$datas[$val['sku']]['type_'.$key] = $val['quantity'];
						}else{
							$datas[$val['sku']]['type_'.$key] = 0;
						}
					}
				}
			}
			$data = array();
			foreach($datas as $key=>$val){
				$data[] = $val;
			}
			$recordsTotal = $recordsFiltered = count($data);
			return compact('data', 'recordsTotal', 'recordsFiltered');
		}
		$data['fromDate'] = date('Y-m-d', time() - 2 * 86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
		$data['reasonType'] = $reasonType;
		return view('analysis/return', ['data' => $data]);
	}

	//asin维度分析
	public function asinAnalysis(Request $req)
	{
		if($_POST){
			$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
			$search = $this->getSearchData(explode('&',$search));
			//搜索条件如下：from_date,to_date
			$where_return = " return_date >= '".$search['from_date']." 00:00:00' and return_date <= '".$search['to_date']." 23:59:59'";
			$where_refund = " where posted_date >= '".$search['from_date']." 00:00:00' and posted_date <= '".$search['to_date']." 23:59:59'";

			if(isset($search['account']) && $search['account']){
				$where_refund.= " and t1.current_seller_account_id in (".$search['account'].")";
			}
			$where = ' where 1 = 1 ';
			if(isset($search['asin']) && $search['asin']){
				$where.= " and tb.asin = '".$search['asin']."'";
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
				group by tb.asin,ta.seller_account_id order by return_quantity desc";

			if($req['length'] != '-1'){//等于-1时为查看全部的数据
				$limit = $this->dtLimit($req);
				$sql .= " LIMIT {$limit} ";
			}

			$datas = DB::connection('amazon')->select($sql);
			$data = json_decode(json_encode($datas),true);
			$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
			$accounts = $this->getAccountInfo();//得到账号机的信息
			foreach($data as $key=>$val) {
				$data[$key]['title'] = $data[$key]['title'] ? $data[$key]['title']: '/NA';
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
				group by tb.sku order by return_quantity desc";

			if($req['length'] != '-1'){//等于-1时为查看全部的数据
				$limit = $this->dtLimit($req);
				$sql .= " LIMIT {$limit} ";
			}

			$datas = DB::connection('amazon')->select($sql);
			$data = json_decode(json_encode($datas),true);
			$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
			foreach($data as $key=>$val) {
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