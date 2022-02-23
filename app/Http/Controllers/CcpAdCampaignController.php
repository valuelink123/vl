<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
	public $typeConfig = array(
							'table' => array('SProducts'=>'ppc_sproducts_campaigns','SDisplay'=>'ppc_sdisplay_campaigns','SBrands'=>'ppc_sbrands_campaigns'),
							'budget_field' => array('SProducts'=>'daily_budget','SDisplay'=>'budget','SBrands'=>'budget'),
						);

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
		$type = array('SProducts'=>'Sponsored Products','SDisplay'=>'Sponsored Display','SBrands'=>'Sponsored Brands');
		$date = $siteDate[current($site)->marketplaceid];
		return view('ccp/ad_campaign',['site'=>$site,'date'=>$date,'siteDate'=>$siteDate,'type'=>$type]);
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
		$type = isset($search['type']) ? $search['type'] : '';
		$this->start_date = isset($search['start_date']) ? $search['start_date'] : '';
		$this->end_date = isset($search['end_date']) ? $search['end_date'] : '';
		$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
		$siteCur = getSiteCur();
		$currency_code = isset($siteCur[$domain]) ? $siteCur[$domain] : '';

		$account_data = $this->getPpcAccountByMarketplace($site);
		$account_id = array_keys($account_data);
		//时间搜索范围
		$where = $this->getPpcDateWhere();
		$where .= " and ppc_profiles.marketplace_id='".$site."' ";
		$where .= " and ppc_profiles.account_id in(".implode(",",$account_id).")";
		if($account){
			$account_str = implode("','", explode(',',$account));
			$where .= " and ppc_profiles.seller_id in('".$account_str."')";
		}

		$table = isset($this->typeConfig['table'][$type]) ? $this->typeConfig['table'][$type] : 'ppc_sproducts_campaigns';

		$sql = "SELECT  
					round(sum(ppc_report_datas.cost),2) as cost,
					round(sum(ppc_report_datas.attributed_sales1d),2) as sales
			FROM
					{$table} as campaigns
			LEFT JOIN ppc_report_datas ON (
					ppc_report_datas.record_type = 'campaign'
					AND campaigns.campaign_id = ppc_report_datas.record_type_id
			)
 			left join ppc_profiles on campaigns.profile_id = ppc_profiles.profile_id
			where ad_type = '".$type."' 
			{$where}";

		$orderData = DB::select($sql);
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
		$limit = '';
		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}
		$sql = $this->getSql($search) .$limit;
		$_data = DB::select($sql);
		$recordsTotal = $recordsFiltered = DB::select('SELECT FOUND_ROWS() as total');
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

	/*
	 * 得到列表和导出数据的sql
	 */
	public function getSql($search)
	{
		$site = isset($search['site']) ? $search['site'] : '';//站点，为marketplaceid
		$account = isset($search['account']) ? $search['account'] : '';//账号id,例如115,137
		$type = isset($search['type']) ? $search['type'] : '';
		$this->start_date = isset($search['start_date']) ? $search['start_date'] : '';
		$this->end_date = isset($search['end_date']) ? $search['end_date'] : '';

		$account_data = $this->getPpcAccountByMarketplace($site);
		$account_id = array_keys($account_data);
		//时间搜索范围
		$where = $this->getPpcDateWhere();
		$where .= " and ppc_profiles.marketplace_id='".$site."' ";
		$where .= " and ppc_profiles.account_id in(".implode(",",$account_id).")";
		if($account){
			$account_str = implode("','", explode(',',$account));
			$where .= " and ppc_profiles.seller_id in('".$account_str."')";
		}
		$table = isset($this->typeConfig['table'][$type]) ? $this->typeConfig['table'][$type] : 'ppc_sproducts_campaigns';
		$budget_field = isset($this->typeConfig['budget_field'][$type]) ? $this->typeConfig['budget_field'][$type] : 'budget';

		$sql = "SELECT SQL_CALC_FOUND_ROWS 
					any_value(ppc_profiles.account_name) as account_name,
       				any_value(ppc_profiles.seller_id) as seller_id,
					campaigns.name as name,
					any_value(campaigns.state) as state,
       				any_value(campaigns.{$budget_field}) as daily_budget,
					round(sum(ppc_report_datas.cost),2) as cost,
					sum(ppc_report_datas.clicks) as clicks,
					round(sum(ppc_report_datas.attributed_sales1d),2) as sales,
					sum(ppc_report_datas.attributed_conversions1d_same_sku) as orders,
					sum(ppc_report_datas.impressions) as impressions
			FROM
					{$table} as campaigns
			LEFT JOIN ppc_report_datas ON (
					ppc_report_datas.record_type = 'campaign'
					AND campaigns.campaign_id = ppc_report_datas.record_type_id
			)
 			left join ppc_profiles on campaigns.profile_id = ppc_profiles.profile_id
			where ad_type = '".$type."' 
			{$where}
			GROUP BY campaigns.name 
			 order by sales desc";
		return $sql;

	}

	/*
	 * 导出列表
	 */
	public function export(Request $req)
	{
		if(!Auth::user()->can(['ccp-ad-campaign-export'])) die('Permission denied -- ccp ad campaign export');
		$site = isset($req['site']) ? $req['site'] : '';//站点，为marketplaceid
		$sql = $this->getSql($req);
		$data_account = getSellerAccout();

		$_data = DB::select($sql);
		//表头

		$headArray = array('ACCOUNT NAME','NAME','STATE','DAILY BUDGET','AD COST','SALES','ORDERS','ACOS','IMPRESSIONS','CLICKS','CTR','CPC','CR');
		$arrayData[] = $headArray;

		foreach($_data as $key=>$val){
			$val = (array)$val;
			$val['acos'] = $val['sales'] > 0 ? sprintf("%.2f",$val['cost']*100/$val['sales']).'%' : '-';
			$val['ctr'] = $val['impressions'] > 0 ? sprintf("%.2f",$val['clicks']*100/$val['impressions']).'%' : '-';
			$val['cpc'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['cost']/$val['clicks']) : '-';
			$val['cr'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['orders']*100/$val['clicks']).'%' : '-';
			$sellerid_marketplaceid = $val['seller_id'].'_'.$site;
			$val['account_name'] = isset($data_account[$sellerid_marketplaceid]) ? $data_account[$sellerid_marketplaceid] : $val['seller_id'];
			$data[$val['name']] = $val;
			$arrayData[] = array(
				$val['account_name'],
				$val['name'],
				$val['state'],
				$val['daily_budget'],
				$val['cost'],
				$val['sales'],
				$val['orders'],
				$val['acos'],
				$val['impressions'],
				$val['clicks'],
				$val['ctr'],
				$val['cpc'],
				$val['cr'],
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
			header('Content-Disposition: attachment;filename="CCP_AdCampaign.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}

}