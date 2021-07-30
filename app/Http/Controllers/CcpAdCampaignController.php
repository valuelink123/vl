<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;


class CcpAdCampaignController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */
	use \App\Traits\DataTables;
	use \App\Traits\Mysqli;

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
		if(!Auth::user()->can(['ccp-ad-campaign-show'])) die('Permission denied -- ccp ad campaign show');
		$site = getMarketDomain();//获取站点选项
		$this->date = date('Y-m-d');

		$siteDate = array();
		foreach($site as $kk=>$vv){
			$siteDate[$vv->marketplaceid] = date('Y-m-d',$this->getCurrentTime($vv->marketplaceid,1));
		}
		$date = $siteDate[current($site)->marketplaceid];
		return view('ccp/ad_campaign',['site'=>$site,'date'=>$date,'siteDate'=>$siteDate]);
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
		$this->start_date = isset($search['start_date']) ? $search['start_date'] : '';
		$this->end_date = isset($search['end_date']) ? $search['end_date'] : '';
		$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
		$siteCur = getSiteCur();
		$currency_code = isset($siteCur[$domain]) ? $siteCur[$domain] : '';

		//时间搜索范围
		$where = $this->getDateWhere();
		$where_profile = " and marketplaces.marketplace = '".$site."'";

		if($account){
			$account_str = implode("','", explode(',',$account));
			$where_profile .= " and accounts.seller_id in('".$account_str."')";
		}

		//sales数据，orders数据
		$sql ="SELECT  
					round(sum(ppc_reports.cost),2) as cost,
					round(sum(ppc_reports.attributed_sales1d),2) as sales
			FROM
					ppc_campaigns
			LEFT JOIN ppc_reports ON (
					ppc_reports.record_type = 'Ppc::Campaign'
					AND ppc_campaigns.campaign_id = ppc_reports.record_type_id
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
		$this->start_date = isset($search['start_date']) ? $search['start_date'] : '';
		$this->end_date = isset($search['end_date']) ? $search['end_date'] : '';
		//时间搜索范围
		$where = $this->getDateWhere();
		$where_profile = " and marketplaces.marketplace = '".$site."'";
		if($account){
			$account_str = implode("','", explode(',',$account));
			$where_profile .= " and accounts.seller_id in('".$account_str."')";
		}

		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}

		$sql = "SELECT SQL_CALC_FOUND_ROWS 
					any_value(accounts.seller_id) as seller_id,
					ppc_campaigns.name as name,
					any_value(ppc_campaigns.state) as state,
					round(sum(ppc_reports.cost),2) as cost,
					sum(ppc_reports.clicks) as clicks,
					round(sum(ppc_reports.attributed_sales1d),2) as sales,
					sum(ppc_reports.attributed_conversions1d_same_sku) as orders,
					sum(ppc_reports.impressions) as impressions
			FROM
					ppc_campaigns
			LEFT JOIN ppc_reports ON (
					ppc_reports.record_type = 'Ppc::Campaign'
					AND ppc_campaigns.campaign_id = ppc_reports.record_type_id
			)
			left join ppc_profiles on ppc_campaigns.profile_id = ppc_profiles.profile_id
			left join accounts on ppc_profiles.account_id = accounts.id 
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
			
			GROUP BY ppc_campaigns.name 
			 order by sales desc {$limit}";


		$_data = DB::connection('ad')->select($sql);
		$recordsTotal = $recordsFiltered = DB::connection('ad')->select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;

		$data_account = getSellerAccout();
		//AD CONVERSION RATE = orders/click  CTR = click/impressions  cpc = sum(cost*clicks)/sum(clicks)  acos=cost/sales
		$data = array();
		foreach($_data as $key=>$val){
			$val = (array)$val;
			$val['acos'] = $val['sales'] > 0 ? sprintf("%.2f",$val['cost']*100/$val['sales']).'%' : '-';
			$val['ctr'] = $val['impressions'] > 0 ? sprintf("%.2f",$val['clicks']*100/$val['impressions']).'%' : '-';
			$val['cpc'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['cost']/$val['clicks']) : '-';
			$val['cr'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['orders']*100/$val['clicks']).'%' : '-';
			$sellerid_marketplaceid = $val['seller_id'].'_'.$site;
			$val['account_name'] = isset($data_account[$sellerid_marketplaceid]) ? $data_account[$sellerid_marketplaceid] : $val['seller_id'];
			$data[$val['name']] = $val;
		}
		$data = array_values($data);
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}


	//得到搜索时间的sql
	public function getDateWhere()
	{
		$startDate = date('Y-m-d',strtotime($this->start_date));//开始时间
		$endDate = date('Y-m-d',strtotime($this->end_date));//结束时间
		$where = " and ppc_reports.date >= '".$startDate."' and ppc_reports.date <= '".$endDate."'";
		return $where;
	}

}