<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class CcpAdProductController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */
	use \App\Traits\DataTables;
	use \App\Traits\Mysqli;

	public $ccpAdmin = array("lidan@valuelinkcorp.com","liuling@dtas.com","wuweiye@valuelinkcorp.com","luodenglin@valuelinkcorp.com","zhouzhiwen@valuelinkltd.com","zhangjianqun@valuelinkcorp.com","sunhanshan@valuelinkcorp.com","wangxiaohua@valuelinkltd.com","zhoulinlin@valuelinkcorp.com","wangshuang@valuelinkltd.com","lixiaojian@valuelinkltd.com");
	public $start_date = '';//搜索时间范围的开始时间
	public $end_date = '';//搜索时间范围的结束时间
	public $typeConfig = array(
		'table_group' => array('SProducts'=>'ppc_sproducts_ad_groups','SDisplay'=>'ppc_sdisplay_ad_groups','SBrands'=>'ppc_sbrands_ad_groups'),
		'table_campaign' => array('SProducts'=>'ppc_sproducts_campaigns','SDisplay'=>'ppc_sdisplay_campaigns','SBrands'=>'ppc_sbrands_campaigns'),
		'table_keyword' => array('SProducts'=>'ppc_sproducts_keywords','SBrands'=>'ppc_sbrands_keywords'),
		'table_product' => array('SProducts'=>'ppc_sproducts_ads','SDisplay'=>'ppc_sdisplay_ads'),
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
		if(!Auth::user()->can(['ccp-ad-product-show'])) die('Permission denied -- ccp ad product show');
		$bgs = $this->queryFields('SELECT DISTINCT bg FROM asin order By bg asc');
		$bus = $this->queryFields('SELECT DISTINCT bu FROM asin order By bu asc');
		$site = getMarketDomain();//获取站点选项
		$this->date = date('Y-m-d');

		$siteDate = array();
		foreach($site as $kk=>$vv){
				$siteDate[$vv->marketplaceid] = date('Y-m-d',$this->getCurrentTime($vv->marketplaceid,1));
		}
		$date = $siteDate[current($site)->marketplaceid];
		$type = array('SProducts'=>'Sponsored Products','SDisplay'=>'Sponsored Display');
		return view('ccp/ad_product',['bgs'=>$bgs,'bus'=>$bus,'site'=>$site,'date'=>$date,'siteDate'=>$siteDate,'type'=>$type]);
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

		$type = isset($search['type']) ? $search['type'] : '';

		//时间搜索范围
		$where = $this->getPpcDateWhere();
		$account_data = $this->getPpcAccountByMarketplace($site);
		$account_id = array_keys($account_data);
		$where .= " and ppc_profiles.marketplace_id='".$site."' ";
		$where .= " and ppc_profiles.account_id in(".implode(",",$account_id).")";
		if($account){
			$account_str = implode("','", explode(',',$account));
			$where .= " and ppc_profiles.seller_id in('".$account_str."')";
		}

		$table_campaign = isset($this->typeConfig['table_campaign'][$type]) ? $this->typeConfig['table_campaign'][$type] : '';
		$table_product = isset($this->typeConfig['table_product'][$type]) ? $this->typeConfig['table_product'][$type] : '';


		//用户权限数据，通过sap_asin_match_sku表得到可查范围的asin_selersku组合数据，
		$asin_sellersku_arr = $this->getSellerSkuData($site,$bg,$bu);
		$asin_sellersku_str = implode("','", $asin_sellersku_arr);
		$where .= " and CONCAT(products.asin,'_',products.sku) in('".$asin_sellersku_str."')";

		$sql = "SELECT  
				round(sum(ppc_report_datas.cost),2) as cost,
				round(sum(case ad_type when 'SProducts' then ppc_report_datas.attributed_sales7d else ppc_report_datas.attributed_sales14d end ),2) as sales
		FROM
				{$table_product} as products
		left join {$table_campaign} as campaigns on products.campaign_id = campaigns.campaign_id 
		LEFT JOIN ppc_report_datas ON (
				ppc_report_datas.record_type = 'ad'
				AND products.ad_id = ppc_report_datas.record_type_id 
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
		$limit = "";
		if($_REQUEST['length']){
				$limit = $this->dtLimit($req);
				$limit = " LIMIT {$limit} ";
		}
		$sql = $this->getSql($search) .$limit;

		$_data = DB::select($sql);
		$recordsTotal = $recordsFiltered = DB::select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;
		$data = $this->getDealData($_data,$site);//得到处理后的数据

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
			$sql_user = "select DISTINCT CONCAT(sap_asin_match_sku.asin,'_',sap_asin_match_sku.seller_sku) as asin_sku from sap_asin_match_sku {$userWhere}
				UNION ALL 
				select DISTINCT CONCAT(asin_match_relation.asin,'_',asin_match_relation.seller_sku) as asin_sku from asin_match_relation {$userWhere}";
			$_data = DB::connection('vlz')->select($sql_user);
			$data = array();
			foreach($_data as $key=>$val){
					$data[] = $val->asin_sku;
			}
			return $data;
	}

	/*
	 * 得到列表和导出数据的sql
	 */
	public function getSql($search)
	{
		$site = isset($search['site']) ? $search['site'] : '';//站点，为marketplaceid
		$account = isset($search['account']) ? $search['account'] : '';//账号id,例如115,137
		$bg = isset($search['bg']) ? $search['bg'] : '';
		$bu = isset($search['bu']) ? $search['bu'] : '';
		$asin = isset($search['asin']) ? trim($search['asin'],'+') : '';//asin输入框的值
		$this->start_date = isset($search['start_date']) ? $search['start_date'] : '';
		$this->end_date = isset($search['end_date']) ? $search['end_date'] : '';
		$type = isset($search['type']) ? $search['type'] : '';

		//时间搜索范围
		$where = $this->getPpcDateWhere();
		$account_data = $this->getPpcAccountByMarketplace($site);
		$account_id = array_keys($account_data);
		$where .= " and ppc_profiles.marketplace_id='".$site."' ";
		$where .= " and ppc_profiles.account_id in(".implode(",",$account_id).")";
		if($account){
			$account_str = implode("','", explode(',',$account));
			$where .= " and ppc_profiles.seller_id in('".$account_str."')";
		}
		$table_campaign = isset($this->typeConfig['table_campaign'][$type]) ? $this->typeConfig['table_campaign'][$type] : '';
		$table_product = isset($this->typeConfig['table_product'][$type]) ? $this->typeConfig['table_product'][$type] : '';

		//用户权限数据，通过sap_asin_match_sku表得到可查范围的asin_selersku组合数据，
		$asin_sellersku_arr = $this->getSellerSkuData($site,$bg,$bu);
		$asin_sellersku_str = implode("','", $asin_sellersku_arr);
		$where .= " and CONCAT(products.asin,'_',products.sku) in('".$asin_sellersku_str."')";
		if($asin){
			$where .= " and products.asin = '".$asin."'";
		}
		$sql = "SELECT  SQL_CALC_FOUND_ROWS 
    					products.asin as asin,
						round(sum(ppc_report_datas.cost),2) as cost,
						sum(ppc_report_datas.clicks) as clicks,
       					round(sum(case ad_type when 'SProducts' then ppc_report_datas.attributed_sales7d else ppc_report_datas.attributed_sales14d end ),2) as sales,
						sum(case ad_type when 'SProducts' then ppc_report_datas.attributed_conversions7d else ppc_report_datas.attributed_conversions14d end ) as orders,
						sum(ppc_report_datas.impressions) as impressions
			FROM
					{$table_product} as products
			left join {$table_campaign} as campaigns on products.campaign_id = campaigns.campaign_id 
			LEFT JOIN ppc_report_datas ON (
					ppc_report_datas.record_type = 'ad'
					AND products.ad_id = ppc_report_datas.record_type_id 
			)
 			left join ppc_profiles on campaigns.profile_id = ppc_profiles.profile_id
			where ad_type = '".$type."' 
			{$where} 
			GROUP BY products.asin 
			 order by sales desc ";
		return $sql;
	}

	/*
	 * 导出列表
	 */
	public function export(Request $req)
	{
		if(!Auth::user()->can(['ccp-ad-product-export'])) die('Permission denied -- ccp ad product export');
		$sql = $this->getSql($req);
		$_data = DB::select($sql);

		//表头
		$headArray = array('PRODUCT','ASIN','Item No.','AD COST','SALES','ORDERS','ACOS','IMPRESSIONS','CLICKS','CTR','CPC','CR');
		$arrayData[] = $headArray;
		$site = isset($req['site']) ? $req['site'] : '';//站点，为marketplaceid
		$data = $this->getDealData($_data,$site);//得到处理后的数据

		foreach($data as $key=>$val){
			$arrayData[] = array(
				$val['title_export'],
				$val['asin_export'],
				$val['item_no'],
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
			header('Content-Disposition: attachment;filename="CCP_AdProduct.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}

	/*
	 * 处理返回的数据
	 */
	public function getDealData($_data,$site)
	{
		$domain = substr(getDomainBySite($site), 4);
		//AD CONVERSION RATE = orders/click  CTR = click/impressions  cpc = sum(cost*clicks)/sum(clicks)  acos=cost/sales
		$data = array();
		$asins = array();
		foreach($_data as $key=>$val){
			$val = (array)$val;
			$asins[] = $val['asin'];
			$val['title'] = $val['title_export'] = $val['item_no'] = $val['image'] = '/NA';
			$val['acos'] = $val['sales'] > 0 ? sprintf("%.2f",$val['cost']*100/$val['sales']).'%' : '-';
			$val['ctr'] = $val['impressions'] > 0 ? sprintf("%.2f",$val['clicks']*100/$val['impressions']).'%' : '-';
			$val['cpc'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['cost']/$val['clicks']) : '-';
			$val['cr'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['orders']*100/$val['clicks']).'%' : '-';
			$data[$val['asin']] = $val;
			$data[$val['asin']]['asin'] = '<a href="https://www.' .$domain. '/dp/' . $val['asin'] .'" target="_blank" rel="noreferrer">'.$val['asin'].'</a>';
			$data[$val['asin']]['asin_export'] = $val['asin'];
		}
		if($asins){
			$asins = "'".implode("','",$asins)."'";
			$product_sql = "select max(title) as title,max(images) as images,asin,max(sku) as item_no
										from asins
										where asin in({$asins})
										and marketplaceid = '{$site}'
										group by asin ";

			$productData = DB::connection('vlz')->select($product_sql);
			foreach($productData as $pkey=>$pval){
				if(isset($data[$pval->asin])){
					$title = mb_substr($pval->title,0,50);
					$data[$pval->asin]['title_export'] = $pval->title;
					$data[$pval->asin]['title'] = '<span title="'.$pval->title.'">'.$title.'</span>';
					$data[$pval->asin]['item_no'] = $pval->item_no ? $pval->item_no : $data[$pval->asin]['item_no'];
					if($pval->images){
						$imageArr = explode(',',$pval->images);
						if($imageArr){
							$image = 'https://images-na.ssl-images-amazon.com/images/I/'.$imageArr[0];
							$data[$pval->asin]['image'] = '<a href="https://www.' .$domain. '/dp/' . $pval->asin .'" target="_blank" rel="noreferrer"><image style="width:50px;height:50px;" src="'.$image.'"></a>';
						}
					}
				}
			}
		}
		$data = array_values($data);
		return $data;
	}

}