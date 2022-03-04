<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;


class CcpAdMatchAsinController extends Controller
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
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		if(!Auth::user()->can(['ccp-adMatchAsin-show'])) die('Permission denied -- ccp adMatchAsin show');
		$site = getMarketDomain();//获取站点选项
		return view('ccp/ad_matchAsin',['site'=>$site]);
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
		$sql = $this->getSql($search). $limit;
		$_data = DB::select($sql);
		$recordsTotal = $recordsFiltered = DB::select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;
		$data = $this->getDealData($_data,$site);
		$data = array_values($data);
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	//导出数据
	public function export()
	{
		if(!Auth::user()->can(['ccp-adMatchAsin-export'])) die('Permission denied -- ccp adMatchAsin export');
		$site = isset($_GET['site']) ? $_GET['site'] : '';
		$sql = $this->getSql($_GET);
		$_data = DB::select($sql);
		$data = $this->getDealData($_data,$site);
		$headArray = array('站点','店铺','Campaign','AD Group','AD Type','ASIN','销售员','SKU');
		$arrayData[] = $headArray;
		foreach($data as $key=>$val){
			$arrayData[] = array(
				$val['site'],
				$val['account_name'],
				$val['campaign'],
				$val['ad_group'],
				$val['ad_type'],
				$val['asin'],
				$val['seller'],
				$val['sku']
			);
		}
		$this->exportExcel($arrayData,"adMatchAsin.xlsx");

	}

	//得到sql语句
	public function getSql($search)
	{
		$site = isset($search['site']) ? $search['site'] : '';//站点，为marketplaceid
		$account = isset($search['account']) ? $search['account'] : '';//账号
		$campaign = isset($search['campaign']) ? $search['campaign'] : '';
		$campaign_name = isset($search['campaign_name']) ? $search['campaign_name'] : '';//campaign_name
		$asin = isset($search['asin']) ? $search['asin'] : '';
		$sku = isset($search['sku']) ? $search['sku'] : '';

		//搜索条件
		$where = '';
		$where .= " and profiles.marketplace_id = '".$site."'";
		if($account){
			$account_str = "'".implode("','", explode(',',$account))."'";
			$where .= " and profiles.seller_id in(".$account_str.")";
		}
		if($campaign){
			$where .= " and union_table.campaign_id in(".$campaign.")";
		}
		if($campaign_name){
			$where .= " and union_table.campaign like '%". $campaign_name ."%'";
		}
		if($asin){
			$where .= " and union_table.asin like '%". $asin ."%'";
		}
		if($sku){
			$where .= " and union_table.sku like '%". $sku ."%'";
		}
		$sql = "SELECT SQL_CALC_FOUND_ROWS union_table.*,profiles.account_name AS account_name,profiles.marketplace_id AS marketplace_id,profiles.seller_id AS seller_id FROM (
	SELECT products.asin as asin,products.sku as sku,groups.name AS ad_group,campaigns.name AS campaign,'sproducts' AS ad_type,campaigns.profile_id AS profile_id,campaigns.campaign_id AS campaign_id,products.ad_id AS ad_id,products.ad_group_id AS ad_group_id,'system' as 'data_type','N/A' as id
	FROM ppc_sproducts_ads as products 
	LEFT JOIN ppc_sproducts_ad_groups as groups ON groups.ad_group_id = products.ad_group_id 
	left join ppc_sproducts_campaigns as campaigns on products.campaign_id = campaigns.campaign_id 
	UNION all
	SELECT products.asin as asin,products.sku as sku,groups.name AS ad_group,campaigns.name AS campaign,'sdisplay' AS ad_type,campaigns.profile_id AS profile_id,campaigns.campaign_id AS campaign_id,products.ad_id AS ad_id,products.ad_group_id AS ad_group_id,'system' as 'data_type','N/A' as id
	FROM ppc_sdisplay_ads as products 
	LEFT JOIN ppc_sdisplay_ad_groups as groups ON groups.ad_group_id = products.ad_group_id 
	left join ppc_sdisplay_campaigns as campaigns on products.campaign_id = campaigns.campaign_id 
	UNION all
	SELECT 'N/A' AS asin,'N/A' AS sku,groups.name AS ad_group,campaigns.name AS campaign,'sbrands' AS ad_type,campaigns.profile_id AS profile_id,campaigns.campaign_id AS campaign_id,0 AS ad_id,groups.ad_group_id AS ad_group_id,'system' AS 'data_type','N/A' AS id
	from ppc_sbrands_campaigns AS campaigns 
	LEFT JOIN ppc_sbrands_ad_groups AS groups ON campaigns.campaign_id = groups.campaign_id 
	LEFT JOIN ppc_ad_match_asin ON ppc_ad_match_asin.campaign_id=campaigns.campaign_id WHERE ppc_ad_match_asin.campaign_id IS null
	UNION ALL
  SELECT products.asin as asin,products.sku as sku,products.ad_group AS ad_group,products.campaign AS campaign,products.ad_type AS ad_type,products.profile_id AS profile_id,products.campaign_id AS campaign_id,products.ad_id AS ad_id,products.ad_group_id AS ad_group_id,'add' as 'data_type',id
  from ppc_ad_match_asin AS products 
 ) AS union_table 
 left join ppc_profiles AS profiles on union_table.profile_id = profiles.profile_id 
 WHERE profiles.marketplace_id IS NOT NULL AND profiles.seller_id IS NOT NULL AND union_table.campaign_id IS NOT NULL {$where}";
		return $sql;
	}

	//得到处理后的数据
	public function getDealData($_data,$site)
	{
		$domain = substr(getDomainBySite($site), 4);
		$data = array();
		$asinInfo = $this->getAsinInfoBySite($site);
		$sap_seller = getUsers('sap_seller');
		foreach($_data as $key=>$val){
			$data[$key] = $val = (array)$val;
			$data[$key]['site'] = $domain;
			if($val['ad_type']=='sbrands' && $val['asin']!='N/A'){
				$data[$key]['action'] = '';
			}else {
				$data[$key]['action'] = '<a href="/ccp/adMatchAsin/add?marketplace_id=' . $val['marketplace_id'] . '&seller_id=' . $val['seller_id'] . '&account_name=' . $val['account_name'] . '&campaign=' . $val['campaign'] . '&ad_group=' . $val['ad_group'] . '&ad_type=' . $val['ad_type'] . '&campaign_id=' . $val['campaign_id'] . '&ad_group_id=' . $val['ad_group_id'] . '&ad_id=' . $val['ad_id'] . '&profile_id=' . $val['profile_id'] . '" class="btn btn-success btn-xs">增加</a>   ';
			}
			if($val['data_type']=='add'){
				$data[$key]['action'] .= '<a href="javascript:void(0);" class="btn btn-success btn-xs" onclick="del('.$val['id'].')">删除</a>';
			}
			$sap_seller_id = '';
			if($val['asin'] && isset($asinInfo[$val['seller_id'].'_'.$val['asin']])){
				$sap_seller_id = $asinInfo[$val['seller_id'].'_'.$val['asin']]['sap_seller_id'];
			}
			$data[$key]['seller'] = isset($sap_seller[$sap_seller_id]) && $sap_seller[$sap_seller_id] ? $sap_seller[$sap_seller_id] : 'N/A';
		}
		return $data;
	}

	/*
	 * 添加数据
	 */
	public function add(Request $request)
	{
		$site = getMarketDomain();//获取站点选项
		$ad_type = array('sproducts','sdisplay','sbrands');
		if($request->isMethod('get')){
			$params = $_GET;
			if(isset($params['marketplace_id'])) {
				$params['domain'] = 'N/A';
				foreach ($site as $key => $val) {
					if ($val->marketplaceid == $params['marketplace_id']) {
						$params['domain'] = $val->domain;
					}
				}
			}
			return view('ccp/ad_matchAsin_add',['params'=>$params,'site'=>$site,'ad_type'=>$ad_type]);
		}elseif ($request->isMethod('post')){
			$insertData = array();
			$configField = array('marketplace_id','seller_id','campaign','ad_group','ad_type','asin','sku','sap_seller_id','campaign_id','ad_group_id','ad_id','profile_id');
			foreach($configField as $field){
				if(isset($_POST[$field]) && $_POST[$field]){
					$insertData[$field] = $_POST[$field];
				}
			}
			if($insertData){
				$res = DB::table('ppc_ad_match_asin')->insert($insertData);
				if($res){
					return redirect('/ccp/adMatchAsin');
				}else{
					$request->session()->flash('error_message','Add Failed');
					return redirect()->back()->withInput();
				}
			}
		}
		return redirect('/ccp/adMatchAsin');
	}

	/*
	 * 删除数据
	 */
	public function delete(Request $request)
	{
		$id = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] : 0;
		$res = DB::table('ppc_ad_match_asin')->where('id',$id)->delete();
		return array('status'=>$res);
	}
}