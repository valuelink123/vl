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
		$data = $_data;
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	//导出数据
	public function export()
	{
		if(!Auth::user()->can(['ccp-adMatchAsin-export'])) die('Permission denied -- ccp adMatchAsin export');
		$site = isset($_GET['site']) ? $_GET['site'] : '';
		$sql = $this->getSql($_GET);
		$data = json_decode(json_encode(DB::select($sql)),true);
		$headArray = array('站点','店铺','Campaign','AD Group','AD Type','ASIN','销售员','SKU');
		$arrayData[] = $headArray;
		foreach($data as $key=>$val){
			$arrayData[] = array(
				$val['site'],
				$val['account_name'],
				$val['campaign_name'],
				$val['group_name'],
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
		$domain = getDomainBySite($site);
		//搜索条件
		$where = '1=1';
		//$where .= "vop_campaign.marketplace_id = '".$site."'";
		if($account){
			$account_str = "'".implode("','", explode(',',$account))."'";
			$where .= " and vop_campaign.seller_id in(".$account_str.")";
		}
		if($campaign){
			$where .= " and vop_campaign.campaign_id in(".$campaign.")";
		}
		if($campaign_name){
			$where .= " and vop_campaign.campaign_name like '%". $campaign_name ."%'";
		}
		if($asin){
			if($asin=='N/A'){
				$where .= " and vop_campaign_asin.asin is NULL";
			}else{
				$where .= " and vop_campaign_asin.asin like '%". $asin ."%'";
			}

		}
		if($sku){
			if($sku=='N/A'){
				$where .= " and vop_campaign_asin.sku is NULL";
			}else{
				$where .= " and vop_campaign_asin.sku like '%". $sku ."%'";
			}
		}

 		$sql = "select SQL_CALC_FOUND_ROWS vop_campaign.*,profiles.account_name AS account_name,vop_campaign_asin.asin as asin,vop_campaign_asin.sku as sku,vop_campaign_asin.id as vop_campaign_asin_id ,vop_asin.seller,'$domain' as site
				from (select * from ppc_ad_campaign WHERE marketplace_id = '$site' ) as vop_campaign
				left join ppc_ad_campaign_match_asin as vop_campaign_asin on vop_campaign.campaign_id = vop_campaign_asin.campaign_id
				left join ppc_profiles AS profiles on vop_campaign.profile_id = profiles.profile_id
				left join 
				(select seller_id,asin,any_value(seller) as seller from asin left join sap_kunnr on asin.sap_store_id=sap_kunnr.kunnr where asin.site='$domain' group by seller_id,asin) as vop_asin
				on `profiles`.seller_id=vop_asin.seller_id and vop_campaign_asin.asin = vop_asin.asin
				WHERE {$where}";

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

			$data[$key]['action'] = '-';
			//if($val['ad_type']=='sbrands'){
			 	if($val['asin']){
					$data[$key]['action'] = '<a href="javascript:void(0);" class="btn btn-success btn-xs" onclick="del('.$val['vop_campaign_asin_id'].')">删除</a>';
				}else{
					$data[$key]['action'] = '<a href="/ccp/adMatchAsin/add?campaign_id=' . $val['campaign_id'] .'" class="btn btn-success btn-xs">增加</a>   ';
				}
			//}
			$sap_seller_id = '';
			if($val['asin'] && isset($asinInfo[$val['seller_id'].'_'.$val['asin']])){
				$sap_seller_id = $asinInfo[$val['seller_id'].'_'.$val['asin']]['sap_seller_id'];
			}
			if($val['asin']==''){
				$data[$key]['asin'] = 'N/A';
			}
			if($val['sku']==''){
				$data[$key]['sku'] = 'N/A';
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
			$campaign_id = $_GET['campaign_id'];
			$sql = "select ppc_ad_campaign.*,profiles.account_name AS account_name from ppc_ad_campaign left join ppc_profiles AS profiles on ppc_ad_campaign.profile_id = profiles.profile_id where campaign_id = {$campaign_id} limit 1";
			$data = DB::select($sql);
			$data = current(json_decode(json_encode($data),true));
			foreach ($site as $key => $val) {
				if ($val->marketplaceid == $data['marketplace_id']) {
					$data['domain'] = $val->domain;
				}
			}
			return view('ccp/ad_matchAsin_add',['data'=>$data]);
		}elseif ($request->isMethod('post')){
			$insertData = array();
			$configField = array('campaign_id','asin','sku','sap_seller_id');
			foreach($configField as $field){
				if(isset($_POST[$field]) && $_POST[$field]){
					$insertData[$field] = trim($_POST[$field]);
				}
			}
			$insertData['user_id'] = intval(Auth::user()->id);
			if($insertData){
				$res = DB::table('ppc_ad_campaign_match_asin')->insert($insertData);
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
		$res = DB::table('ppc_ad_campaign_match_asin')->where('id',$id)->delete();
		return array('status'=>$res);
	}
}
