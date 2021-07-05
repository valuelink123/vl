<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;


class CcpAdController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */
	use \App\Traits\DataTables;
	use \App\Traits\Mysqli;

	public $ccpAdmin = array("xumeiling@valuelinkcorp.com","lidan@valuelinkcorp.com","liuling@dtas.com","wuweiye@valuelinkcorp.com","luodenglin@valuelinkcorp.com","zhouzhiwen@valuelinkltd.com","zhangjianqun@valuelinkcorp.com","sunhanshan@valuelinkcorp.com","wangxiaohua@valuelinkltd.com","zhoulinlin@valuelinkcorp.com","wangshuang@valuelinkltd.com");
	public $start_date = '';//搜索时间范围的开始时间
	public $end_date = '';//搜索时间范围的结束时间

	public function __construct()
	{
		$this->middleware('auth');
		parent::__construct();
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		if(!Auth::user()->can(['ccp-ad-show'])) die('Permission denied -- ccp ad show');
		$bgs = $this->queryFields('SELECT DISTINCT bg FROM asin order By bg asc');
		$bus = $this->queryFields('SELECT DISTINCT bu FROM asin order By bu asc');
		$site = getMarketDomain();//获取站点选项
		$date = $this->date = date('Y-m-d');

		$siteDate = array();
		foreach($site as $kk=>$vv){
			$siteDate[$vv->marketplaceid] = date('Y-m-d',$this->getCurrentTime($vv->marketplaceid,1));
		}
		$date = $siteDate[current($site)->marketplaceid];

		return view('ccp/ad',['bgs'=>$bgs,'bus'=>$bus,'site'=>$site,'date'=>$date,'siteDate'=>$siteDate]);
	}
	/*
	* 获得统计总数据
	 */
	public function showTotal()
	{
	    //搜索条件，统计数据不受下面的asin搜索的影响
        $search = isset($_REQUEST['search_data']) ? $_REQUEST['search_data'] : '';
        $search = $this->getSearchData(explode('&',$search));
        $site = isset($search['site']) ? $search['site'] : '';//站点，为marketplaceid
        $account = isset($search['account']) ? $search['account'] : '';//账号id,seller_id
		$bg = isset($search['bg']) ? $search['bg'] : '';
		$bu = isset($search['bu']) ? $search['bu'] : '';
		$this->start_date = isset($search['start_date']) ? $search['start_date'] : '';
		$this->end_date = isset($search['end_date']) ? $search['end_date'] : '';
		$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
		$siteCur = getSiteCur();
		$currency_code = isset($siteCur[$domain]) ? $siteCur[$domain] : '';

		//时间搜索范围
		$where = $this->getDateWhere($site);
		$where_profile = " and marketplaces.marketplace = '".$site."'";

		if($account){
			$account_str = implode("','", explode(',',$account));
			$where_profile .= " and accounts.seller_id in('".$account_str."')";
		}

		//用户权限数据，通过sap_asin_match_sku表得到可查范围的asin_selersku组合数据，
		$asin_sellersku_arr = $this->getSellerSkuData($site,$bg,$bu);
		$asin_sellersku_str = implode("','", $asin_sellersku_arr);
		$where .= " and CONCAT(ppc_product_ads.asin,'_',ppc_product_ads.sku) in('".$asin_sellersku_str."')";


		//sales数据，orders数据
		$sql ="SELECT  
					round(sum(ppc_reports.cost*ppc_reports.clicks),2) as cost,
					round(sum(ppc_reports.attributed_conversions1d_same_sku*ppc_reports.attributed_sales1d),2) as sales
				FROM
					ppc_product_ads
				LEFT JOIN ppc_reports ON (
					ppc_reports.record_type = 'Ppc::ProductAd'
					AND ppc_product_ads.ad_id = ppc_reports.record_type_id
				)
				WHERE
					ppc_reports.profile_id IN (
						SELECT
							ppc_profiles.profile_id
						FROM
							accounts,
							ppc_profiles,
							marketplaces
						WHERE
							accounts.user_id = 8566
						AND ppc_profiles.account_id = accounts.id
						AND accounts.marketplace_id = marketplaces.id 
					    {$where_profile}
					)
				{$where}";

		$orderData = DB::connection('ad')->select($sql);
		$array = array(
			'sales' => round($orderData[0]->sales,2),
			'cost' => round($orderData[0]->cost,2),
			'danwei' => $currency_code,
		);
		return $array;
	}

	//展示列表数据
	public function list(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
        $site = isset($search['site']) ? $search['site'] : '';//站点，为marketplaceid
        $account = isset($search['account']) ? $search['account'] : '';//账号id,例如115,137
		$bg = isset($search['bg']) ? $search['bg'] : '';
		$bu = isset($search['bu']) ? $search['bu'] : '';
        $asin = isset($search['asin']) ? trim($search['asin'],'+') : '';//asin输入框的值
		$this->start_date = isset($search['start_date']) ? $search['start_date'] : '';
		$this->end_date = isset($search['end_date']) ? $search['end_date'] : '';
		//时间搜索范围
		$where = $this->getDateWhere($site);
		$where_profile = " and marketplaces.marketplace = '".$site."'";
		if($account){
			$account_str = implode("','", explode(',',$account));
			$where_profile .= " and accounts.seller_id in('".$account_str."')";
		}

		//用户权限数据，通过sap_asin_match_sku表得到可查范围的asin_selersku组合数据，
		$asin_sellersku_arr = $this->getSellerSkuData($site,$bg,$bu);
		$asin_sellersku_str = implode("','", $asin_sellersku_arr);
		$where .= " and CONCAT(ppc_product_ads.asin,'_',ppc_product_ads.sku) in('".$asin_sellersku_str."')";
		if($asin){
			$where .= " and asin = '".$asin."'";
		}

		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
					ppc_product_ads.asin,
					round(sum(ppc_reports.cost*ppc_reports.clicks),2) as cost,
					sum(ppc_reports.clicks) as clicks,
					round(sum(ppc_reports.attributed_conversions1d_same_sku*ppc_reports.attributed_sales1d),2) as sales,
					sum(ppc_reports.attributed_conversions1d_same_sku) as orders,
					sum(ppc_reports.impressions) as impressions
				FROM
					ppc_product_ads
				LEFT JOIN ppc_reports ON (
					ppc_reports.record_type = 'Ppc::ProductAd'
					AND ppc_product_ads.ad_id = ppc_reports.record_type_id
				)
				WHERE
					ppc_reports.profile_id IN (
						SELECT
							ppc_profiles.profile_id
						FROM
							accounts,
							ppc_profiles,
							marketplaces
						WHERE
							accounts.user_id = 8566
						AND ppc_profiles.account_id = accounts.id
						AND accounts.marketplace_id = marketplaces.id 
					    {$where_profile}
				
					)
				{$where}
				
				GROUP BY ppc_product_ads.asin 
				 order by sales desc {$limit}";


		$_data = DB::connection('ad')->select($sql);
		$recordsTotal = $recordsFiltered = DB::connection('ad')->select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;

		//AD CONVERSION RATE = orders/click  CTR = click/impressions  cpc = sum(cost*clicks)/sum(clicks)  acos=cost/sales
		$data = array();
		foreach($_data as $key=>$val){
			$val = (array)$val;
			$val['acos'] = $val['sales'] > 0 ? sprintf("%.2f",$val['cost']*100/$val['sales']).'%' : '-';
			$val['ctr'] = $val['impressions'] > 0 ? sprintf("%.2f",$val['clicks']*100/$val['impressions']).'%' : '-';
			$val['cpc'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['cost']*100/$val['clicks']).'%' : '-';
			$val['cr'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['orders']*100/$val['clicks']).'%' : '-';
			$data[] = $val;
		}

		return compact('data', 'recordsTotal', 'recordsFiltered');
	}
	/*
	 * 用户权限数据，通过sap_asin_match_sku表得到可查范围的asin_selersku组合数据
	 */
	public function getSellerSkuData($site,$bg,$bu)
	{
		$userdata = Auth::user();
		$userWhere = " where LENGTH(asin)=10 and marketplace_id  = '".$site."'";
		if (!in_array(Auth::user()->email, $this->ccpAdmin)) {
			if ($userdata->seller_rules) {
				$rules = explode("-", $userdata->seller_rules);
				if (array_get($rules, 0) != '*') $userWhere .= " and sap_seller_bg = '".array_get($rules, 0)."'";
				if (array_get($rules, 1) != '*') $userWhere .= " and sap_seller_bu = '".array_get($rules, 1)."'";
			}elseif($userdata->sap_seller_id){
				$userWhere .= " and sap_seller_id = ".$userdata->sap_seller_id;
			}
		}

		if($bg){
			$userWhere .= " and sap_seller_bg = '".$bg."'";
		}
		if($bu){
			$userWhere .= " and sap_seller_bu = '".$bu."'";
		}
		$sql_user = " select DISTINCT CONCAT(sap_asin_match_sku.asin,'_',sap_asin_match_sku.seller_sku) as asin_sku  from sap_asin_match_sku {$userWhere}";
		$_data = DB::connection('vlz')->select($sql_user);
		$data = array();
		foreach($_data as $key=>$val){
			$data[] = $val->asin_sku;
		}
		return $data;
	}

	//得到搜索时间的sql
	public function getDateWhere($site)
	{
		$startDate = date('Y-m-d 00:00:00',strtotime($this->start_date));//开始时间
		$endDate = date('Y-m-d 23:59:59',strtotime($this->end_date));//结束时间
		$dateconfig = array('A1PA6795UKMFR9','A1RKKUPIHCS9HS','A13V1IB3VIYZZH','APJ6JRA9NG5V4');//utc+2:00
		if($site=='A1VC38T7YXB528'){//日本站点，date字段+9hour
			$date_field = 'date_add(ppc_reports.created_at,INTERVAL 9 hour) ';
		}elseif($site=='A1F83G8C2ARO7P'){//英国站点+1小时，uTc+1:00
			$date_field = 'date_add(ppc_reports.created_at,INTERVAL 1 hour) ';
		}elseif(in_array($site,$dateconfig)){//站点+2小时，utc+2:00
			$date_field = 'date_add(ppc_reports.created_at,INTERVAL 2 hour) ';
		}else{//其他站点，date字段-7hour
			$date_field = 'date_sub(ppc_reports.created_at,INTERVAL 7 hour) ';
		}
		$where = " and {$date_field} BETWEEN STR_TO_DATE( '".$startDate."', '%Y-%m-%d %H:%i:%s' ) AND STR_TO_DATE('".$endDate."', '%Y-%m-%d %H:%i:%s' )";
		return $where;
	}

}