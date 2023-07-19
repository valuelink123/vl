<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class CcpAdTargetController extends Controller
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
		'table_group' => array('SProducts'=>'ppc_sproducts_ad_groups','SDisplay'=>'ppc_sdisplay_ad_groups','SBrands'=>'ppc_sbrands_ad_groups'),
		'table_campaign' => array('SProducts'=>'ppc_sproducts_campaigns','SDisplay'=>'ppc_sdisplay_campaigns','SBrands'=>'ppc_sbrands_campaigns'),
		'table_keyword' => array('SProducts'=>'ppc_sproducts_keywords','SBrands'=>'ppc_sbrands_keywords'),
		'table_product' => array('SProducts'=>'ppc_sproducts_ads','SDisplay'=>'ppc_sdisplay_ads'),
		'table_target' => array('SProducts'=>'ppc_sproducts_targets','SDisplay'=>'ppc_sdisplay_targets','SBrands'=>'ppc_sbrands_targets'),
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
		if(!Auth::user()->can(['ccp-ad-target-show'])) die('Permission denied -- ccp ad target show');
		$site = getMarketDomain();//获取站点选项
		$this->date = date('Y-m-d');

		$siteDate = array();
		foreach($site as $kk=>$vv){
			$siteDate[$vv->marketplaceid] = date('Y-m-d',$this->getCurrentTime($vv->marketplaceid,1));
		}
		$type = array('SProducts'=>'Sponsored Products','SDisplay'=>'Sponsored Display','SBrands'=>'Sponsored Brands');
		$date = $siteDate[current($site)->marketplaceid];
		return view('ccp/ad_target',['site'=>$site,'date'=>$date,'siteDate'=>$siteDate,'type'=>$type]);
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

		$table_target = isset($this->typeConfig['table_target'][$type]) ? $this->typeConfig['table_target'][$type] : '';
		$table_campaign = isset($this->typeConfig['table_campaign'][$type]) ? $this->typeConfig['table_campaign'][$type] : '';
		$table_group = isset($this->typeConfig['table_group'][$type]) ? $this->typeConfig['table_group'][$type] : '';

		$left = " left join {$table_campaign} as campaigns on targets.campaign_id = campaigns.campaign_id ";
		if($type=='SDisplay'){
			$left = " left join {$table_group} as groups on targets.ad_group_id = groups.ad_group_id
			 left join {$table_campaign} as campaigns on campaigns.campaign_id = groups.campaign_id ";
		}
		$str_sales = "round(sum(case ad_type when 'SProducts' then ppc_report_datas.attributed_sales7d else ppc_report_datas.attributed_sales14d end ),2) as sales";
		if($type==='SDisplay'){
			$str_sales = "round(sum(case ad_type when 'SProducts' then ppc_report_datas.attributed_sales7d when 'SDisplay' and campaigns.cost_type='VCPM' then ppc_report_datas.view_attributed_sales14d else ppc_report_datas.attributed_sales14d end ),2) as sales";
		}
		$sql = "SELECT  
					round(sum(ppc_report_datas.cost),2) as cost,
					{$str_sales}
			FROM
					{$table_target} as targets
			{$left}
			LEFT JOIN ppc_report_datas ON (
					ppc_report_datas.record_type = 'target'
					AND targets.target_id = ppc_report_datas.record_type_id 
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
		$limit = "";
		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}
		$sql = $this->getSql($search) .$limit;

		$_data = DB::select($sql);
		$recordsTotal = $recordsFiltered = DB::select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;

		//AD CONVERSION RATE = orders/click  CTR = click/impressions  cpc = sum(cost*clicks)/sum(clicks)  acos=cost/sales
		$data = array();
		foreach($_data as $key=>$val){
			$val = (array)$val;
			$val['acos'] = $val['sales'] > 0 ? sprintf("%.2f",$val['cost']*100/$val['sales']).'%' : '-';
			$val['ctr'] = $val['impressions'] > 0 ? sprintf("%.2f",$val['clicks']*100/$val['impressions']).'%' : '-';
			$val['cpc'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['cost']/$val['clicks']) : '-';
			$val['cr'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['orders']*100/$val['clicks']).'%' : '-';
			$val['bid'] = sprintf("%.2f",$val['bid']);
			$data[$val['target_id']] = $val;
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
		$table_target = isset($this->typeConfig['table_target'][$type]) ? $this->typeConfig['table_target'][$type] : '';
		$table_campaign = isset($this->typeConfig['table_campaign'][$type]) ? $this->typeConfig['table_campaign'][$type] : '';
		$table_group = isset($this->typeConfig['table_group'][$type]) ? $this->typeConfig['table_group'][$type] : '';

		$left = " left join {$table_campaign} as campaigns on targets.campaign_id = campaigns.campaign_id ";
		if($type=='SDisplay'){
			$left = " left join {$table_group} as groups on targets.ad_group_id = groups.ad_group_id
			 left join {$table_campaign} as campaigns on campaigns.campaign_id = groups.campaign_id ";
		}

		$str_sales = "round(sum(case ad_type when 'SProducts' then ppc_report_datas.attributed_sales7d else ppc_report_datas.attributed_sales14d end ),2) as sales";
		$str_orders = "sum(case ad_type when 'SProducts' then ppc_report_datas.attributed_conversions7d else ppc_report_datas.attributed_conversions14d end ) as orders";
		$str_impressions = "sum(ppc_report_datas.impressions) as impressions";
		if($type==='SDisplay'){
			$str_sales = "round(sum(case ad_type when 'SProducts' then ppc_report_datas.attributed_sales7d when 'SDisplay' and campaigns.cost_type='VCPM' then ppc_report_datas.view_attributed_sales14d else ppc_report_datas.attributed_sales14d end ),2) as sales";
			$str_orders = "sum(case ad_type when 'SProducts' then ppc_report_datas.attributed_conversions7d when 'SDisplay' and campaigns.cost_type='VCPM' then ppc_report_datas.view_attributed_conversions14d else ppc_report_datas.attributed_conversions14d end ) as orders";
			$str_impressions = "sum(case ad_type when 'SDisplay' and campaigns.cost_type='VCPM' then ppc_report_datas.view_impressions else ppc_report_datas.impressions end ) as impressions";
		}
		$sql = "SELECT  SQL_CALC_FOUND_ROWS 
					any_value(targets.target_id) as target_id,
					any_value(targets.bid) as bid,
					any_value(targets.state) as state,
					round(sum(ppc_report_datas.cost),2) as cost,
					sum(ppc_report_datas.clicks) as clicks,
					{$str_sales},
					{$str_orders},
					{$str_impressions}
			FROM
					{$table_target} as targets
			{$left}
			LEFT JOIN ppc_report_datas ON (
					ppc_report_datas.record_type = 'target'
					AND targets.target_id = ppc_report_datas.record_type_id 
			)
 			left join ppc_profiles on campaigns.profile_id = ppc_profiles.profile_id
			where ad_type = '".$type."' 
			{$where} 
			GROUP BY targets.target_id 
			 order by sales desc ";
		return $sql;
	}

	/*
	 * 导出列表
	 */
	public function export(Request $req)
	{
		if(!Auth::user()->can(['ccp-ad-target-export'])) die('Permission denied -- ccp ad target export');
		$sql = $this->getSql($req);
		$_data = DB::select($sql);

		//表头
		$headArray = array('TARGET ID','BID','STATE','AD COST','SALES','ORDERS','ACOS','IMPRESSIONS','CLICKS','CTR','CPC','CR');
		$arrayData[] = $headArray;

		foreach($_data as $key=>$val){
			$val = (array)$val;
			$val['acos'] = $val['sales'] > 0 ? sprintf("%.2f",$val['cost']*100/$val['sales']).'%' : '-';
			$val['ctr'] = $val['impressions'] > 0 ? sprintf("%.2f",$val['clicks']*100/$val['impressions']).'%' : '-';
			$val['cpc'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['cost']/$val['clicks']) : '-';
			$val['cr'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['orders']*100/$val['clicks']).'%' : '-';
			$val['bid'] = sprintf("%.2f",$val['bid']);
			$data[$val['target_id']] = $val;
			$arrayData[] = array(
				$val['target_id'].' ',
				$val['bid'],
				$val['state'],
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
			header('Content-Disposition: attachment;filename="CCP_AdTarget.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}

}