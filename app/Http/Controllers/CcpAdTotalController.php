<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;


class CcpAdTotalController extends Controller
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
	 * 展示列表页面
	 */
	public function adTotalBgIndex()
	{
		if(!Auth::user()->can(['ccp-adTotalBg-show'])) die('Permission denied -- ccp adTotalBg show');
		$start_date = date('Y-m-d');
		$end_date = date('Y-m-d');
		$site = getMarketDomain();//获取站点选项
		return view('ccp/ad_total_bg',['start_date'=>$start_date,'end_date'=>$end_date,'site'=>$site]);
	}

	//展示列表数据
	public function adTotalBgList(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$start_date = isset($search['start_date']) ? $search['start_date'] : '';
		$end_date = isset($search['end_date']) ? $search['end_date'] : '';
		$site = isset($search['site']) ? $search['site'] : '';

//		$start_date = '2021-08-01';//测试时间
//		$end_date = '2021-12-01';//测试时间
		$_data = $this->getAdData($site,$start_date,$end_date);//得到站点广告数据

		//求ccp的站内总销售额
//		$start_date = $end_date = '2021-01-19';//测试时间
		$_sales = $this->getCcpData($site,$start_date,$end_date);
		$bgs = $this->getBg();
		$asinData = $this->getSapAsinMatchSkuInfo();

		$data = array();
		//各个bg的参数先赋值
		foreach($bgs as $bgk=>$bgv){
			$data[$bgv['bg']]['bg'] = $bgv['bg'];
			$data[$bgv['bg']]['ad_cost'] = $data[$bgv['bg']]['ad_sales'] = $data[$bgv['bg']]['total_sales'] = $data[$bgv['bg']]['actual_sales']= 0.00;

		}
		//相加求和
		foreach($_data as $key=>$val){
			$_key = $val['marketplace_id'].'_'.$val['seller_id'].'_'.$val['asin'];
			if(isset($asinData[$_key]) && $asinData[$_key]){
				$bg = $asinData[$_key]['sap_seller_bg'];
				$data[$bg]['ad_cost'] += $val['ad_cost'];
				$data[$bg]['ad_sales'] += $val['ad_sales'];
			}
		}
		foreach($_sales as $key=>$val){
			$_key = $val['marketplace_id'].'_'.$val['seller_id'].'_'.$val['asin'];
			if(isset($asinData[$_key]) && $asinData[$_key]){
				$bg = $asinData[$_key]['sap_seller_bg'];
				$data[$bg]['total_sales'] += $val['sales'];
			}
		}
		//需要用总和的数据来计算的，acos = 站内纯广告费用/纯广告销售额比例，，actual_sales = 总销售额扣除15%（退货10%和VAT的5%)，，站内纯广告费用/总销售额(实际)
		$total_ad_cost = $total_ad_sales = $total_total_sales = 0.00;
		if($data) {
			foreach ($data as $key => $val) {
				$data[$key]['ad_cost'] = sprintf("%.2f", $val['ad_cost']);
				$data[$key]['ad_sales'] = sprintf("%.2f", $val['ad_sales']);
				$data[$key]['total_sales'] = sprintf("%.2f", $val['total_sales']);
				$data[$key]['ad_acos'] = $val['ad_sales'] > 0 ? sprintf("%.2f", $val['ad_cost'] * 100 / $val['ad_sales']) . '%' : '-';
				$data[$key]['actual_sales'] = sprintf("%.2f", $val['total_sales'] * 0.85);
				$data[$key]['acos'] = $data[$key]['actual_sales'] > 0 ? sprintf("%.2f", $val['ad_cost'] * 100 / $data[$key]['actual_sales']) . '%' : '-';

				//总计相加
				$total_ad_cost += $data[$key]['ad_cost'];
				$total_ad_sales += $data[$key]['ad_sales'];
				$total_total_sales += $data[$key]['total_sales'];
			}

			//总计的算法
			$data['total']['bg'] = '总计';
			$data['total']['ad_cost'] = sprintf("%.2f", $total_ad_cost);
			$data['total']['ad_sales'] = sprintf("%.2f", $total_ad_sales);
			$data['total']['total_sales'] = sprintf("%.2f", $total_total_sales);

			$data['total']['ad_acos'] = $data['total']['ad_sales'] > 0 ? sprintf("%.2f", $data['total']['ad_cost'] * 100 / $data['total']['ad_sales']) . '%' : '-';
			$data['total']['actual_sales'] = sprintf("%.2f", $data['total']['total_sales'] * 0.85);
			$data['total']['acos'] = $data['total']['actual_sales'] > 0 ? sprintf("%.2f", $data['total']['ad_cost'] * 100 / $data['total']['actual_sales']) . '%' : '-';
		}


		$data = array_values($data);
		return compact('data');
	}

	/**
	 * 展示列表页面
	 */
	public function adTotalBuIndex()
	{
		if(!Auth::user()->can(['ccp-adTotalBu-show'])) die('Permission denied -- ccp adTotalBu show');
		$start_date = date('Y-m-d');
		$end_date = date('Y-m-d');
		$site = getMarketDomain();//获取站点选项
		$userdata = Auth::user();
		$rules = explode("-", $userdata->seller_rules);
		$bg = '';
		$ccpAdmin = $this->getccpAdmin();
		if (!in_array($userdata->email, $ccpAdmin)) {
			if (array_get($rules, 0) != '*') {
				$bg = array_get($rules, 0);
			}
		}
		$bgs = $this->getBg($bg);
		return view('ccp/ad_total_bu',['start_date'=>$start_date,'end_date'=>$end_date,'site'=>$site,'bgs'=>$bgs]);
	}

	//展示列表数据
	public function adTotalBuList(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$start_date = isset($search['start_date']) ? $search['start_date'] : '';
		$end_date = isset($search['end_date']) ? $search['end_date'] : '';
		$site = isset($search['site']) ? $search['site'] : '';
		$bg = isset($search['bg']) ? $search['bg'] : '';

//		$start_date = '2021-08-01';//测试时间
//		$end_date = '2021-12-01';//测试时间
		$_data = $this->getAdData($site,$start_date,$end_date,$bg);//得到站点广告数据

		//求ccp的站内总销售额
//		$start_date = $end_date = '2021-01-19';//测试时间
		$_sales = $this->getCcpData($site,$start_date,$end_date,$bg);
		$bus = $this->getBu($bg);
		$asinData = $this->getSapAsinMatchSkuInfo($site,$bg);
		$data = array();
		//各个bg的参数先赋值
		foreach($bus as $bgk=>$bgv){
			$department = $bgv['bg'].'-'.$bgv['bu'];
			$data[$department]['department'] = $department;
			$data[$department]['ad_cost'] = $data[$department]['ad_sales'] = $data[$department]['total_sales'] = $data[$department]['actual_sales']= 0.00;
		}
		//相加求和
		foreach($_data as $key=>$val){
			$_key = $val['marketplace_id'].'_'.$val['seller_id'].'_'.$val['asin'];
			if(isset($asinData[$_key]) && $asinData[$_key]){
				$department = $asinData[$_key]['sap_seller_bg'].'-'.$asinData[$_key]['sap_seller_bu'];
				$data[$department]['ad_cost'] += $val['ad_cost'];
				$data[$department]['ad_sales'] += $val['ad_sales'];
			}
		}
		foreach($_sales as $key=>$val){
			$_key = $val['marketplace_id'].'_'.$val['seller_id'].'_'.$val['asin'];
			if(isset($asinData[$_key]) && $asinData[$_key]){
				$department = $asinData[$_key]['sap_seller_bg'].'-'.$asinData[$_key]['sap_seller_bu'];
				$data[$department]['total_sales'] += $val['sales'];
			}
		}
		//需要用总和的数据来计算的，acos = 站内纯广告费用/纯广告销售额比例，，actual_sales = 总销售额扣除15%（退货10%和VAT的5%)，，站内纯广告费用/总销售额(实际)
		$total_ad_cost = $total_ad_sales = $total_total_sales = 0.00;
		if($data) {
			foreach ($data as $key => $val) {
				$data[$key]['ad_cost'] = sprintf("%.2f", $val['ad_cost']);
				$data[$key]['ad_sales'] = sprintf("%.2f", $val['ad_sales']);
				$data[$key]['total_sales'] = sprintf("%.2f", $val['total_sales']);

				$data[$key]['ad_acos'] = $val['ad_sales'] > 0 ? sprintf("%.2f", $val['ad_cost'] * 100 / $val['ad_sales']) . '%' : '-';
				$data[$key]['actual_sales'] = sprintf("%.2f", $val['total_sales'] * 0.85);
				$data[$key]['acos'] = $data[$key]['actual_sales'] > 0 ? sprintf("%.2f", $val['ad_cost'] * 100 / $data[$key]['actual_sales']) . '%' : '-';

				//总计相加
				$total_ad_cost += $data[$key]['ad_cost'];
				$total_ad_sales += $data[$key]['ad_sales'];
				$total_total_sales += $data[$key]['total_sales'];
			}
			//总计的算法
			$data['total']['department'] = '总计';
			$data['total']['ad_cost'] = sprintf("%.2f", $total_ad_cost);
			$data['total']['ad_sales'] = sprintf("%.2f", $total_ad_sales);
			$data['total']['total_sales'] = sprintf("%.2f", $total_total_sales);

			$data['total']['ad_acos'] = $data['total']['ad_sales'] > 0 ? sprintf("%.2f", $data['total']['ad_cost'] * 100 / $data['total']['ad_sales']) . '%' : '-';
			$data['total']['actual_sales'] = sprintf("%.2f", $data['total']['total_sales'] * 0.85);
			$data['total']['acos'] = $data['total']['actual_sales'] > 0 ? sprintf("%.2f", $data['total']['ad_cost'] * 100 / $data['total']['actual_sales']) . '%' : '-';
		}

		$data = array_values($data);
		return compact('data');
	}

	/**
	 * 展示列表页面（销售员维度）
	 */
	public function adTotalSellerIndex()
	{
		if(!Auth::user()->can(['ccp-adTotalSeller-show'])) die('Permission denied -- ccp adTotalSeller show');
		$start_date = date('Y-m-d');
		$end_date = date('Y-m-d');
		$site = getMarketDomain();//获取站点选项
		$userdata = Auth::user();
		$rules = explode("-", $userdata->seller_rules);
		$bg = $bu = '';
		$ccpAdmin = $this->getccpAdmin();
		if (!in_array($userdata->email, $ccpAdmin)) {
			if (array_get($rules, 0) != '*') {
				$bg = array_get($rules, 0);
			}
			if (array_get($rules, 1) != '*') {
				$bu = array_get($rules, 1);
			}
		}
		$bgs = $this->getBg($bg);
		$_bus = $this->getBu($bg,$bu);
		$bus = array();
		foreach($_bus as $key=>$val){
			$bus[$val['bu']] = $val['bu'];
		}
		return view('ccp/ad_total_seller',['start_date'=>$start_date,'end_date'=>$end_date,'site'=>$site,'bgs'=>$bgs,'bus'=>$bus]);
	}

	//展示列表数据（销售员维度）
	public function adTotalSellerList(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$start_date = isset($search['start_date']) ? $search['start_date'] : '';
		$end_date = isset($search['end_date']) ? $search['end_date'] : '';
		$site = isset($search['site']) ? $search['site'] : '';
		$bg = isset($search['bg']) ? $search['bg'] : '';
		$bu = isset($search['bu']) ? $search['bu'] : '';

//		$start_date = '2021-08-01';//测试时间
//		$end_date = '2021-12-01';//测试时间
		$_data = $this->getAdData($site,$start_date,$end_date,$bg,$bu);//得到站点广告数据

		//求ccp的站内总销售额
//		$start_date = $end_date = '2021-01-19';//测试时间
		$_sales = $this->getCcpData($site,$start_date,$end_date,$bg,$bu);
		$bus = $this->getBu($bg);
		$asinData = $this->getSapAsinMatchSkuInfo($site,$bg,$bu);
		$data = array();
		//各个bg的参数先赋值
		$sellerData = getUsers('sap_seller');
		//相加求和
		foreach($_data as $key=>$val){
			$_key = $val['marketplace_id'].'_'.$val['seller_id'].'_'.$val['asin'];
			if(isset($asinData[$_key]) && $asinData[$_key]){
				$department = $asinData[$_key]['sap_seller_id'].'-'.$asinData[$_key]['sku'];
				$data[$department]['seller'] = isset($sellerData[$asinData[$_key]['sap_seller_id']]) ? $sellerData[$asinData[$_key]['sap_seller_id']] : $asinData[$_key]['sap_seller_id'];
				$data[$department]['sku'] = $asinData[$_key]['sku'];
				if(isset($data[$department]['ad_cost'])){
					$data[$department]['ad_cost'] += $val['ad_cost'];
				}else{
					$data[$department]['ad_cost'] = $val['ad_cost'];
				}
				if(isset($data[$department]['ad_sales'])){
					$data[$department]['ad_sales'] += $val['ad_sales'];
				}else{
					$data[$department]['ad_sales'] = $val['ad_sales'];
				}
				if(!isset($data[$department]['total_sales'])){
					$data[$department]['total_sales'] = 0.00;
				}
			}
		}
		foreach($_sales as $key=>$val){
			$_key = $val['marketplace_id'].'_'.$val['seller_id'].'_'.$val['asin'];
			if(isset($asinData[$_key]) && $asinData[$_key]){
				$department = $asinData[$_key]['sap_seller_id'].'-'.$asinData[$_key]['sku'];
				$data[$department]['seller'] = isset($sellerData[$asinData[$_key]['sap_seller_id']]) ? $sellerData[$asinData[$_key]['sap_seller_id']] : $asinData[$_key]['sap_seller_id'];
				$data[$department]['sku'] = $asinData[$_key]['sku'];
				if(isset($data[$department]['total_sales'])){
					$data[$department]['total_sales'] += $val['sales'];
				}else{
					$data[$department]['total_sales'] = $val['sales'];
				}
				if(!isset($data[$department]['ad_cost'])){
					$data[$department]['ad_cost'] = 0.00;
				}
				if(!isset($data[$department]['ad_sales'])){
					$data[$department]['ad_sales'] = 0.00;
				}
			}
		}
		//需要用总和的数据来计算的，acos = 站内纯广告费用/纯广告销售额比例，，actual_sales = 总销售额扣除15%（退货10%和VAT的5%)，，站内纯广告费用/总销售额(实际)
		//总计的参数初始化
		$total_ad_cost = $total_ad_sales = $total_total_sales = 0.00;
		if($data) {
			foreach ($data as $key => $val) {
				$data[$key]['ad_cost'] = sprintf("%.2f", $val['ad_cost']);
				$data[$key]['ad_sales'] = sprintf("%.2f", $val['ad_sales']);
				$data[$key]['total_sales'] = sprintf("%.2f", $val['total_sales']);

				$data[$key]['ad_acos'] = $val['ad_sales'] > 0 ? sprintf("%.2f", $val['ad_cost'] * 100 / $val['ad_sales']) . '%' : '-';
				$data[$key]['actual_sales'] = sprintf("%.2f", $val['total_sales'] * 0.85);
				$data[$key]['acos'] = $data[$key]['actual_sales'] > 0 ? sprintf("%.2f", $val['ad_cost'] * 100 / $data[$key]['actual_sales']) . '%' : '-';
				//总计相加
				$total_ad_cost += $data[$key]['ad_cost'];
				$total_ad_sales += $data[$key]['ad_sales'];
				$total_total_sales += $data[$key]['total_sales'];
			}

			//总计的算法
			$data['total']['seller'] = '总计';
			$data['total']['sku'] = '-';
			$data['total']['ad_cost'] = sprintf("%.2f", $total_ad_cost);
			$data['total']['ad_sales'] = sprintf("%.2f", $total_ad_sales);
			$data['total']['total_sales'] = sprintf("%.2f", $total_total_sales);

			$data['total']['ad_acos'] = $data['total']['ad_sales'] > 0 ? sprintf("%.2f", $data['total']['ad_cost'] * 100 / $data['total']['ad_sales']) . '%' : '-';
			$data['total']['actual_sales'] = sprintf("%.2f", $data['total']['total_sales'] * 0.85);
			$data['total']['acos'] = $data['total']['actual_sales'] > 0 ? sprintf("%.2f", $data['total']['ad_cost'] * 100 / $data['total']['actual_sales']) . '%' : '-';
		}

		$data = array_values($data);
		return compact('data');
	}


	/*
	 * 得到广告数据
	 */
	public function getAdData($site,$start_date,$end_date,$bg='',$bu='')
	{
		$userAsins = $this->getUserAsin($site,$bg,$bu);
		$userAsins_str = implode("','",$userAsins);
		$sql="select SQL_CALC_FOUND_ROWS union_table.marketplace_id,union_table.seller_id,union_table.asin,sum(union_table.cost) as ad_cost,sum(union_table.attributed_sales1d) as ad_sales from (SELECT data.`date`,data.impressions,data.clicks,data.cost,data.attributed_sales1d,data.attributed_units_ordered1d,ads.asin,ads.sku,profile.seller_id,profile.marketplace_id,profile.account_name
FROM ppc_report_datas as data
LEFT JOIN ppc_profiles as profile ON (data.profile_id=profile.profile_id)
LEFT JOIN ppc_sproducts_ads as ads ON (ads.ad_id=data.record_type_id )
WHERE data.date BETWEEN '".$start_date."' AND '".$end_date."'
AND data.ad_type='SProducts' AND data.record_type='ad' 
AND profile.marketplace_id='".$site."' 
UNION ALL 
SELECT data.`date`,data.impressions,data.clicks,data.cost,data.attributed_sales1d,data.attributed_units_ordered1d,ads.asin,ads.sku,profile.seller_id,profile.marketplace_id,profile.account_name
FROM ppc_report_datas as data
LEFT JOIN ppc_profiles as profile ON (data.profile_id=profile.profile_id)
LEFT JOIN ppc_sdisplay_ads as ads ON (ads.ad_id=data.record_type_id )
WHERE data.date BETWEEN '".$start_date."' AND '".$end_date."'
AND data.ad_type='SDisplay' AND data.record_type='ad' 
AND profile.marketplace_id='".$site."' 
    ) as union_table 
    where CONCAT(union_table.asin,'_',union_table.sku)  in('".$userAsins_str."') 
    group by union_table.marketplace_id,union_table.seller_id,union_table.asin";
//		echo $sql;exit;
		$_data = DB::select($sql);
		$_data = json_decode(json_encode($_data),true);
		return $_data;
	}

	/*
	 * 求ccp的站内总销售额
	 */
	public function getCcpData($site,$start_date,$end_date,$bg='',$bu='')
	{
		$userAsins = $this->getUserAsin($site,$bg,$bu,'asin');
		$userAsins_str = implode("','",$userAsins);
		$orderwhere = $where = $this->getCcpDateWhere($site,1,$start_date,$end_date);
		$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
		$orderwhere .= " and sales_channel = '".ucfirst($domain)."'";
		$sql_sales ="SELECT kk.marketplace_id as marketplace_id,seller_accounts.mws_seller_id as seller_id,kk.asin as asin,SUM( item_price_amount ) AS sales
			FROM
			  (SELECT order_items.amazon_order_id,asin_price.marketplace_id,asin_price.seller_account_id,order_items.asin,asin_price.price AS default_unit_price,order_items.quantity_ordered,
			  		CASE order_items.item_price_amount WHEN 0.00 THEN asin_price.price * order_items.quantity_ordered ELSE order_items.item_price_amount END AS item_price_amount 
			  	FROM order_items 
				LEFT JOIN asin_price ON order_items.seller_account_id = asin_price.seller_account_id   AND order_items.asin = asin_price.asin  AND asin_price.marketplace_id = '".$site."'
			  	WHERE order_items.amazon_order_id IN 
				  (
					SELECT amazon_order_id 
					FROM orders 
					WHERE order_status IN ( 'PendingAvailability', 'Pending', 'Unshipped', 'PartiallyShipped', 'Shipped', 'InvoiceUnconfirmed', 'Unfulfillab' ) {$orderwhere}
				  )
			  	{$where} 
			) AS kk 
			left join seller_accounts on seller_accounts.id=kk.seller_account_id 
			where kk.asin in('".$userAsins_str."') 
			GROUP BY kk.marketplace_id,seller_accounts.mws_seller_id,kk.asin ";
		$_sales = DB::connection('vlz')->select($sql_sales);
		$_sales = json_decode(json_encode($_sales),true);
		return $_sales;
	}

}