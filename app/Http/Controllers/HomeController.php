<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Groupdetail;
use App\User;
use App\Task;
use App\Asin;
use App\SkusDailyInfo;
use App\AsinDailyInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

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
    public function index(Request $request)
    {
		$limit_bg = $limit_bu = $limit_sap_seller_id = $limit_review_user_id='';
		
		$sumwhere = '1=1';
		$bonus_point = 0;
		if (Auth::user()->seller_rules) {
			$rules = explode("-", Auth::user()->seller_rules);
			if (array_get($rules, 0) != '*'){
				$limit_bg = array_get($rules, 0);
				$bonus_point = 0.1;
			}else{
				$bonus_point = 1;
			}
			if (array_get($rules, 1) != '*'){
				$limit_bu = array_get($rules, 1);
				$bonus_point = 0.3;
			}
		} elseif (Auth::user()->sap_seller_id) {
			$limit_sap_seller_id = Auth::user()->sap_seller_id;
			$bonus_point = 0.6;
		} else {
			$limit_review_user_id = Auth::user()->id;
			$bonus_point = 0.04;
		}
		
		
		
		$asins = DB::table( DB::raw("(select max(sku_ranking) as sku_ranking,max(rating) as rating,max(review_count) as review_count,max(item_no) as item_no,sum(fba_stock) as fba_stock,sum(fba_transfer) as fba_transfer,max(fbm_stock) as fbm_stock,sum(sales_07_01) as sales_07_01,sum(sales_14_08) as sales_14_08,sum(sales_21_15) as sales_21_15,sum(sales_28_22) as sales_28_22,max(bg) as bg,max(bu) as bu,max(sap_seller_id) as sap_seller_id,max(review_user_id) as review_user_id, min(case when status = 'S' Then '0' else status end) as status,asin,site from asin where length(asin)=10 group by asin,site) as asin") )->orderByRaw("status asc,sales_07_01 desc")
		->leftJoin( DB::raw("(select asin as asin_b,domain,avg(sessions) as sessions,avg(unit_session_percentage) as unit_session_percentage,avg(bsr) as bsr from star_history where create_at >= '".date('Y-m-d',strtotime('-10day'))."' group by asin,domain) as asin_star") ,function($q){
			$q->on('asin.asin', '=', 'asin_star.asin_b')->on('asin.site', '=', 'asin_star.domain');
		})->leftJoin("skus_status" ,function($q){
			$q->on('asin.item_no', '=', 'skus_status.sku')->on('asin.site', '=', 'skus_status.site');
		})->selectRaw("asin.*,asin_star.*,skus_status.status as sku_status,skus_status.level as sku_level");
		if($limit_bg){
			$asins = $asins->where('asin.bg',$limit_bg);
			$sumwhere.=" and bg='$limit_bg'";
		}
		if($limit_bu){
			$asins = $asins->where('asin.bu',$limit_bu);
			$sumwhere.=" and bu='$limit_bu'";
		}
		if($limit_sap_seller_id){
			$asins = $asins->where('asin.sap_seller_id',$limit_sap_seller_id);
			$sumwhere.=" and sap_seller_id='$limit_sap_seller_id'";
		}
		if($limit_review_user_id){
			$asins = $asins->where('asin.review_user_id',$limit_review_user_id);
			$sumwhere.=" and review_user_id='$limit_review_user_id'";
		}
		
		$user_teams = DB::select("select bg,bu from asin where $sumwhere group by bg,bu ORDER BY BG ASC,BU ASC");
		
		if(array_get($_REQUEST,'user_id')){
			$user_info = User::where('id',array_get($_REQUEST,'user_id'))->first();
			
			if ($user_info->seller_rules) {
				$rules = explode("-", $user_info->seller_rules);
				if (array_get($rules, 0) != '*'){
					$limit_bg = array_get($rules, 0);
					$bonus_point = 0.1;
				}else{
					$bonus_point = 1;
				}
				if (array_get($rules, 1) != '*'){
					$limit_bu = array_get($rules, 1);
					$bonus_point = 0.3;
				}
			} elseif ($user_info->sap_seller_id) {
				$limit_sap_seller_id = $user_info->sap_seller_id;
				$bonus_point = 0.6;
			} else {
				$limit_review_user_id = $user_info->id;
				$bonus_point = 0.04;
			}
		}
		if($limit_bg){
			$asins = $asins->where('asin.bg',$limit_bg);
			$sumwhere.=" and bg='$limit_bg'";
		}
		if($limit_bu){
			$asins = $asins->where('asin.bu',$limit_bu);
			$sumwhere.=" and bu='$limit_bu'";
		}
		if($limit_sap_seller_id){
			$asins = $asins->where('asin.sap_seller_id',$limit_sap_seller_id);
			$sumwhere.=" and sap_seller_id='$limit_sap_seller_id'";
		}
		if($limit_review_user_id){
			$asins = $asins->where('asin.review_user_id',$limit_review_user_id);
			$sumwhere.=" and review_user_id='$limit_review_user_id'";
		}

		if(array_get($_REQUEST,'bgbu')){
			$bgbu = array_get($_REQUEST,'bgbu');
			$bgbu_arr = explode('_',$bgbu);
			if(array_get($bgbu_arr,0)) {
				$asins = $asins->where('asin.bg',array_get($bgbu_arr,0));
				$sumwhere.=" and bg='".array_get($bgbu_arr,0)."'";
			}
			if(array_get($bgbu_arr,1)){
				$asins = $asins->where('asin.bu',array_get($bgbu_arr,1));
				$sumwhere.=" and bu='".array_get($bgbu_arr,1)."'";
			}
		}
		
		
		
		
		
		
		$fba_stock_info = DB::select("select sum(fba_stock*fba_cost) as fba_total_amount,sum(fba_stock) as fba_total_stock from (select avg(fba_stock+fba_transfer) as fba_stock,avg(fba_cost) as fba_cost from asin where $sumwhere group by asin,sellersku ) as fba_total");
		
		$fbm_stock_info = DB::select("select sum(fbm_stock*fbm_cost) as fbm_total_amount,sum(fbm_stock) as fbm_total_stock from (select avg(fbm_stock) as fbm_stock,avg(fbm_cost) as fbm_cost from asin where $sumwhere group by item_no) as fbm_total");
		
		
		$asins = $asins->take(5)->get();
		$asins = json_decode(json_encode($asins), true);
		$date_from = $request->get('date_from')?$request->get('date_from'):(date('Y-m',strtotime('-2days')).'-01');
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d',strtotime('-2days'));
		
		if($date_to>date('Y-m-d',strtotime('-2days'))) $date_to=date('Y-m-d',strtotime('-2days'));
		if($date_from>$date_to) $date_from=$date_to;
		$hb_date_to = date('Y-m-d',strtotime($date_from)-86400);
		$hb_date_from = date('Y-m-d',2*strtotime($date_from)-strtotime($date_to)-86400);
		
		$total_info = SkusDailyInfo::select(DB::raw('sum(bonus)*'.$bonus_point.' as bonus,sum(economic) as economic,sum(amount) as amount,sum(sales) as sales'))->whereRaw($sumwhere." and date>='$date_from' and date<='$date_to'")->first()->toArray();
		
		$hb_total_info = SkusDailyInfo::select(DB::raw('sum(bonus)*'.$bonus_point.' as bonus,sum(economic) as economic,sum(amount) as amount,sum(sales) as sales'))->whereRaw($sumwhere." and date>='$hb_date_from' and date<='$hb_date_to'")->first()->toArray();
		
		$daily_info = SkusDailyInfo::select(DB::raw('sum(bonus)*'.$bonus_point.' as sumbonus,date'))->whereRaw($sumwhere." and date>='$date_from' and date<='$date_to'")->groupBy(['date'])->pluck('sumbonus','date');
		
		foreach($asins as $key=>$asin){
			$sku_info = SkusDailyInfo::select(DB::raw('sum(bonus)*'.$bonus_point.' as bonus,sum(economic) as economic'))->whereRaw("sku='".$asin['item_no']."' and site='".$asin['site']."' and date>='$date_from' and date<='$date_to'")->first()->toArray();
			$asins[$key]['bonus']=array_get($sku_info,'bonus',0);
			$asins[$key]['economic']=array_get($sku_info,'economic',0);
			
			$asin_info = AsinDailyInfo::select(DB::raw('sum(amount) as amount,sum(sales) as sales'))->whereRaw("asin='".$asin['asin']."' and site='".$asin['site']."' and date>='$date_from' and date<='$date_to'")->first()->toArray();
			$asins[$key]['amount']=array_get($asin_info,'amount',0);
			$asins[$key]['sales']=array_get($asin_info,'sales',0);
		}
	
		$returnDate['bonus_point']= $bonus_point;
		$returnDate['total_info']= $total_info;
		$returnDate['hb_total_info']= $hb_total_info;
		$returnDate['daily_info']= $daily_info;
		$returnDate['teams']= $user_teams;
		$returnDate['users']= $this->getUsers();
		$returnDate['date_from']= $date_from;
		$returnDate['date_to']= $date_to;
		$returnDate['s_user_id']= $request->get('user_id');
		$returnDate['bgbu']= $request->get('bgbu');
		
		$returnDate['fba_stock_info']= $fba_stock_info;
		$returnDate['fbm_stock_info']= $fbm_stock_info;
		$returnDate['asins']= $asins;
		$returnDate['tasks']= Task::where('response_user_id',Auth::user()->id)->where('stage','<',3)->take(10)->orderBy('priority','desc')->get()->toArray();
        return view('home',$returnDate);

    }
	
	public function getUsers(){
        $users = User::get();
		
		if (Auth::user()->seller_rules) {
			$rules = explode("-", Auth::user()->seller_rules);
			if (array_get($rules, 0) != '*'){
				$users = $users->where('ubg',array_get($rules, 0));
			}else{
			}
			if (array_get($rules, 1) != '*'){
				$users = $users->where('ubu',array_get($rules, 1));
			}
		} elseif (Auth::user()->sap_seller_id) {
			$users = $users->where('sap_seller_id',Auth::user()->sap_seller_id);
		} else {
			$users = $users->where('id',Auth::user()->id);
		}
		$users = $users->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }
	
	
	public function getSellers(){
        $users = User::where('sap_seller_id','>',0)->get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['sap_seller_id']] = $user['name'];
        }
        return $users_array;
    }
	
	public function asins(Request $request){
		$limit_bg = $limit_bu = $limit_sap_seller_id = $limit_review_user_id='';
		$sumwhere = '1=1';
		if (Auth::user()->seller_rules) {
			$rules = explode("-", Auth::user()->seller_rules);
			if (array_get($rules, 0) != '*'){
				$limit_bg = array_get($rules, 0);
			}else{
			}
			if (array_get($rules, 1) != '*'){
				$limit_bu = array_get($rules, 1);
			}
		} elseif (Auth::user()->sap_seller_id) {
			$limit_sap_seller_id = Auth::user()->sap_seller_id;
		} else {
			$limit_review_user_id = Auth::user()->id;
		}
		if($limit_bg){
			$sumwhere.=" and bg='$limit_bg'";
		}
		if($limit_bu){
			$sumwhere.=" and bu='$limit_bu'";
		}
		if($limit_sap_seller_id){
			$sumwhere.=" and sap_seller_id='$limit_sap_seller_id'";
		}
		if($limit_review_user_id){
			$sumwhere.=" and review_user_id='$limit_review_user_id'";
		}
        $date_from = $request->get('date_from')?$request->get('date_from'):(date('Y-m',strtotime('-2days')).'-01');
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d',strtotime('-2days'));
		$teams = DB::select("select bg,bu from asin where $sumwhere group by bg,bu ORDER BY BG ASC,BU ASC");
		$users = $this->getUsers();
		if($date_to>date('Y-m-d',strtotime('-2days'))) $date_to=date('Y-m-d',strtotime('-2days'));
		if($date_from>$date_to) $date_from=$date_to;
		$s_user_id = $request->get('user_id');
		$bgbu = $request->get('bgbu');
		return view('asin',compact('date_from','date_to','s_user_id','bgbu','teams','users'));	
    }
	
	public function getasins(Request $request){
        $limit_bg = $limit_bu = $limit_sap_seller_id = $limit_review_user_id='';
		$date_from = $request->get('date_from')?$request->get('date_from'):(date('Y-m',strtotime('-2days')).'-01');
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d',strtotime('-2days'));
		$sumwhere = '1=1';
		$bonus_point = 0;
		if (Auth::user()->seller_rules) {
			$rules = explode("-", Auth::user()->seller_rules);
			if (array_get($rules, 0) != '*'){
				$limit_bg = array_get($rules, 0);
				$bonus_point = 0.1;
			}else{
				$bonus_point = 1;
			}
			if (array_get($rules, 1) != '*'){
				$limit_bu = array_get($rules, 1);
				$bonus_point = 0.3;
			}
		} elseif (Auth::user()->sap_seller_id) {
			$limit_sap_seller_id = Auth::user()->sap_seller_id;
			$bonus_point = 0.6;
		} else {
			$limit_review_user_id = Auth::user()->id;
			$bonus_point = 0.04;
		}
		
		
		
		$asins = DB::table( DB::raw("(select max(sku_ranking) as sku_ranking,max(rating) as rating,max(review_count) as review_count,max(item_no) as item_no,sum(fba_stock) as fba_stock,sum(fba_transfer) as fba_transfer,max(fbm_stock) as fbm_stock,sum(sales_07_01) as sales_07_01,sum(sales_14_08) as sales_14_08,sum(sales_21_15) as sales_21_15,sum(sales_28_22) as sales_28_22,max(bg) as bg,max(bu) as bu,max(sap_seller_id) as sap_seller_id,max(review_user_id) as review_user_id, min(case when status = 'S' Then '0' else status end) as status,asin,site from asin where length(asin)=10 group by asin,site) as asin") )
		->leftJoin( DB::raw("(select asin as asin_b,domain,avg(sessions) as sessions,avg(unit_session_percentage) as unit_session_percentage,avg(bsr) as bsr from star_history where create_at >= '".date('Y-m-d',strtotime('-10day'))."' group by asin,domain) as asin_star") ,function($q){
			$q->on('asin.asin', '=', 'asin_star.asin_b')->on('asin.site', '=', 'asin_star.domain');
		})->leftJoin( DB::raw("(select sum(amount) as amount,sum(sales) as sales,asin as asin_a,site as site_a from asin_daily_info where date>='$date_from' and date<='$date_to' group by asin,site) as asin_d") ,function($q){
			$q->on('asin.asin', '=', 'asin_d.asin_a')->on('asin.site', '=', 'asin_d.site_a');
		})->leftJoin( DB::raw("(select sum(bonus) as bonus,sum(economic) as economic,sku as sku_s,site  as site_s from skus_daily_info where date>='$date_from' and date<='$date_to' group by sku,site) as sku_d") ,function($q){
			$q->on('asin.item_no', '=', 'sku_d.sku_s')->on('asin.site', '=', 'sku_d.site_s');
		})->leftJoin("skus_status" ,function($q){
			$q->on('asin.item_no', '=', 'skus_status.sku')->on('asin.site', '=', 'skus_status.site');
		})->selectRaw("asin.*,asin_star.*,asin_d.*,sku_d.*,skus_status.status as sku_status,skus_status.level as sku_level");;
		
		
		
		if(array_get($_REQUEST,'user_id')){
			$user_info = User::where('id',array_get($_REQUEST,'user_id'))->first();
			
			if ($user_info->seller_rules) {
				$rules = explode("-", $user_info->seller_rules);
				if (array_get($rules, 0) != '*'){
					$limit_bg = array_get($rules, 0);
					$bonus_point = 0.1;
				}else{
					$bonus_point = 1;
				}
				if (array_get($rules, 1) != '*'){
					$limit_bu = array_get($rules, 1);
					$bonus_point = 0.3;
				}
			} elseif ($user_info->sap_seller_id) {
				$limit_sap_seller_id = $user_info->sap_seller_id;
				$bonus_point = 0.6;
			} else {
				$limit_review_user_id = $user_info->id;
				$bonus_point = 0.04;
			}
		}
		if(array_get($_REQUEST,'bgbu')){
			$bgbu = array_get($_REQUEST,'bgbu');
			$bgbu_arr = explode('_',$bgbu);
			if(array_get($bgbu_arr,0)) {
				$asins = $asins->where('asin.bg',array_get($bgbu_arr,0));
				$sumwhere.=" and bg='".array_get($bgbu_arr,0)."'";
			}
			if(array_get($bgbu_arr,1)){
				$asins = $asins->where('asin.bu',array_get($bgbu_arr,1));
				$sumwhere.=" and bu='".array_get($bgbu_arr,1)."'";
			}
		}
		if(array_get($_REQUEST,'site')){
			$asins = $asins->whereIn('asin.site',$_REQUEST['site']);
		}
		if(array_get($_REQUEST,'keywords')){
            $keywords = array_get($_REQUEST,'keywords');
            $asins = $asins->where(function ($query) use ($keywords) {
                $query->where('asin.item_no',$keywords)
					  ->orwhere('asin.asin',$keywords);

            });
        }
		
		if($limit_bg){
			$asins = $asins->where('asin.bg',$limit_bg);
			$sumwhere.=" and bg='$limit_bg'";
		}
		if($limit_bu){
			$asins = $asins->where('asin.bu',$limit_bu);
			$sumwhere.=" and bu='$limit_bu'";
		}
		if($limit_sap_seller_id){
			$asins = $asins->where('asin.sap_seller_id',$limit_sap_seller_id);
			$sumwhere.=" and sap_seller_id='$limit_sap_seller_id'";
		}
		if($limit_review_user_id){
			$asins = $asins->where('asin.review_user_id',$limit_review_user_id);
			$sumwhere.=" and review_user_id='$limit_review_user_id'";
		}
		
		$orderby = 'sales';
        $sort = 'desc';
		
        if(isset($_REQUEST['order'][0])){
           
			if($_REQUEST['order'][0]['column']==3) $orderby = 'asin_d.amount';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'asin_d.sales';
			if($_REQUEST['order'][0]['column']==5) $orderby = 'asin_d.sales';
           
			if($_REQUEST['order'][0]['column']==6) $orderby = '(case when asin_d.sales = 0 Then 0 else asin_d.amount/asin_d.sales end)';
			
			if($_REQUEST['order'][0]['column']==7) $orderby = 'asin.fba_stock';
			if($_REQUEST['order'][0]['column']==8) $orderby = 'asin.fba_transfer';
			if($_REQUEST['order'][0]['column']==10) $orderby = 'asin.fbm_stock';
			if($_REQUEST['order'][0]['column']==11) $orderby = 'asin.rating';
			if($_REQUEST['order'][0]['column']==12) $orderby = 'asin.review_count';
			if($_REQUEST['order'][0]['column']==13) $orderby = 'asin_star.sessions';
			if($_REQUEST['order'][0]['column']==14) $orderby = 'asin_star.unit_session_percentage';
			if($_REQUEST['order'][0]['column']==15) $orderby = 'asin.sku_ranking';
			if($_REQUEST['order'][0]['column']==16) $orderby = 'asin_star.bsr';
			if($_REQUEST['order'][0]['column']==17) $orderby = 'sku_d.economic';
			if($_REQUEST['order'][0]['column']==18) $orderby = 'sku_d.bonus';
			
            $sort = $_REQUEST['order'][0]['dir'];
        }

		$iTotalRecords = $asins->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
		$datas =  $asins->orderByRaw($orderby.' '.$sort)->skip($iDisplayStart)->take($iDisplayLength)->get()->toArray();
		$datas = json_decode(json_encode($datas),true);
        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;
		

		
		$users_array = $this->getUsers();
		$sellers_array = $this->getSellers();
        foreach ( $datas as $asin){
			$sales = ((((array_get($asin,'sales_07_01')??array_get($asin,'sales_14_08'))??array_get($asin,'sales_21_15'))??array_get($asin,'sales_28_22'))??0)/7 ;
			$records["data"][] = array(
				'<a href="https://'.array_get($asin,'site').'/dp/'.array_get($asin,'asin').'" class="primary-link" target="_blank">'.array_get($asin,'asin').'</a>',
				array_get($asin,'item_no'),
				array_get($asin,'sku_status'),
				round(array_get($asin,'amount'),2),
				round(array_get($asin,'sales'),2),
				intval(array_get($asin,'sales')/((strtotime($date_to)-strtotime($date_from))/86400+1)),
				(array_get($asin,'sales')>0)?round(array_get($asin,'amount')/array_get($asin,'sales'),2):0,
				array_get($asin,'fba_stock',0),
				array_get($asin,'fba_transfer'),
				($sales>0)?date('Y-m-d',strtotime('+'.intval((array_get($asin,'fba_stock',0)+array_get($asin,'fba_transfer',0))/$sales).' days')):'∞',
				array_get($asin,'fbm_stock',0),
				array_get($asin,'rating',0),
				array_get($asin,'review_count',0),
				intval(array_get($asin,'sessions')),
				round(array_get($asin,'unit_session_percentage'),2),
				substr(array_get($asin,'sku_ranking'),0,20),
				intval(array_get($asin,'bsr')),
				intval(array_get($asin,'economic',0)),
				intval(array_get($asin,'bonus')*$bonus_point)
			);
        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
		
    }
	
	public function Export(Request $request){
		$limit_bg = $limit_bu = $limit_sap_seller_id = $limit_review_user_id='';
		
		$sumwhere = '1=1';
		$bonus_point = 0;
		if (Auth::user()->seller_rules) {
			$rules = explode("-", Auth::user()->seller_rules);
			if (array_get($rules, 0) != '*'){
				$limit_bg = array_get($rules, 0);
				$bonus_point = 0.1;
			}else{
				$bonus_point = 1;
			}
			if (array_get($rules, 1) != '*'){
				$limit_bu = array_get($rules, 1);
				$bonus_point = 0.3;
			}
			if(array_get($_REQUEST,'bgbu')){
				$bgbu = array_get($_REQUEST,'bgbu');
				$bgbu_arr = explode('_',$bgbu);
				if(array_get($bgbu_arr,0)) {
					$sumwhere.=" and bg='".array_get($bgbu_arr,0)."'";
				}
				if(array_get($bgbu_arr,1)){
					$sumwhere.=" and bu='".array_get($bgbu_arr,1)."'";
				}
			}
			
			
			if(array_get($_REQUEST,'user_id')){
				$user_info = User::where('id',array_get($_REQUEST,'user_id'))->first();
				
				if ($user_info->seller_rules) {
					$rules = explode("-", $user_info->seller_rules);
					if (array_get($rules, 0) != '*'){
						$limit_bg = array_get($rules, 0);
						$bonus_point = 0.1;
					}else{
						$bonus_point = 1;
					}
					if (array_get($rules, 1) != '*'){
						$limit_bu = array_get($rules, 1);
						$bonus_point = 0.3;
					}
				} elseif ($user_info->sap_seller_id) {
					$limit_sap_seller_id = $user_info->sap_seller_id;
					$bonus_point = 0.6;
				} else {
					$limit_review_user_id = $user_info->id;
					$bonus_point = 0.04;
				}
			}
		} elseif (Auth::user()->sap_seller_id) {
			$limit_sap_seller_id = Auth::user()->sap_seller_id;
			$bonus_point = 0.6;
		} else {
			$limit_review_user_id = Auth::user()->id;
			$bonus_point = 0.04;
		}
		
		if($limit_bg){

			$sumwhere.=" and bg='$limit_bg'";
		}
		if($limit_bu){

			$sumwhere.=" and bu='$limit_bu'";
		}
		if($limit_sap_seller_id){

			$sumwhere.=" and sap_seller_id='$limit_sap_seller_id'";
		}
		if($limit_review_user_id){

			$sumwhere.=" and review_user_id='$limit_review_user_id'";
		}
		
		
		$date_from = $request->get('date_from')?$request->get('date_from'):(date('Y-m',strtotime('-2days')).'-01');
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d',strtotime('-2days'));
		
		if($date_to>date('Y-m-d',strtotime('-2days'))) $date_to=date('Y-m-d',strtotime('-2days'));
		if($date_from>$date_to) $date_from=$date_to;
		
		$date_month = date('Y-m',strtotime($date_from));

		$total_info = SkusDailyInfo::select(DB::raw('sku,site,sum(amount) as amount,sum(sales) as sales,sum((cost+tax+headshipfee)*sales) as total_cost,sum(fulfillmentfee) as fulfillmentfee,sum(commission) as commission,sum(otherfee) as otherfee,sum(returnqty) as returnqty,sum(deal) as deal,sum(coupon) as coupon,sum(cpc) as cpc,sum(fbm_storage) as fbm_storage,sum(fba_storage) as fba_storage,sum(amount_used) as amount_used,sum(economic) as economic,sum(profit) as profit,sum(amount_target) as amount_target,sum(sales_target) as sales_target,sum(profit_target) as profit_target,sum(bonus) as bonus'))->whereRaw($sumwhere." and date>='$date_from' and date<='$date_to'")->groupBy(['sku','site'])->get()->toArray();
		
		
		
		$spreadsheet = new Spreadsheet();
		$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Total');
		$spreadsheet->addSheet($myWorkSheet, 0);
		$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Details');
		$spreadsheet->addSheet($myWorkSheet, 1);
		$arrayData=[];
		
		$arrayData[] = ['物料号','站点','订单金额','销量','总成本','操作费','交易费','其他费用','退货数量','Deal营销费','Coupon营销费','CPC营销费','FBM仓储费','FBA仓储费','资金占用成本','经济效益','业务净利润','销量目标','销量完成率','销售额目标','销售额完成率','利润目标','利润完成率','提成基数'];
		foreach($total_info as $val){
		
			if(array_get($val,'sales_target')<0){
				$sales_per = round(2-array_get($val,'sales')/array_get($val,'sales_target'),4);
			}elseif(array_get($val,'sales_target')>0){
				$sales_per = round(array_get($val,'sales')/array_get($val,'sales_target'),4);
			}else{
				$sales_per =0;
			}
			
			if(array_get($val,'amount_target')<0){
				$amount_per = round(2-array_get($val,'amount')/array_get($val,'amount_target'),4);
			}elseif(array_get($val,'amount_target')>0){
				$amount_per =round(array_get($val,'amount')/array_get($val,'amount_target'),4);
			}else{
				$amount_per =0;
			}
			
			if(array_get($val,'profit_target')<0){
				$profit_per = round(2-array_get($val,'profit')/array_get($val,'profit_target'),4);
			}elseif(array_get($val,'profit_target')>0){
				$profit_per =round(array_get($val,'profit')/array_get($val,'profit_target'),4);
			}else{
				$profit_per =0;
			}
			$arrayData[] = [
				array_get($val,'sku'),
				array_get($val,'site'),
				array_get($val,'amount'),
				array_get($val,'sales'),
				round(array_get($val,'total_cost'),2),
				array_get($val,'fulfillmentfee'),
				array_get($val,'commission'),
				array_get($val,'otherfee'),
				array_get($val,'returnqty'),
				array_get($val,'deal'),
				array_get($val,'coupon'),
				array_get($val,'cpc'),
				array_get($val,'fbm_storage'),
				array_get($val,'fba_storage'),
				array_get($val,'amount_used'),
				array_get($val,'economic'),
				array_get($val,'profit'),
				array_get($val,'sales_target'),
				$sales_per,
				array_get($val,'amount_target'),
				$amount_per,
				array_get($val,'profit_target'),
				$profit_per,
				array_get($val,'bonus')
			];
		}
		$spreadsheet->getSheet(0)->fromArray($arrayData,NULL,'A1');
		
		$arrayData=[];
		$daily_info = SkusDailyInfo::whereRaw($sumwhere." and date>='$date_from' and date<='$date_to'")->orderby('date','asc')->get()->toArray();
		$size_set = ['0'=>'标准','1'=>'大件'];
		$type_set = ['0'=>'淘汰','1'=>'保留','2'=>'新品'];
		$seller_set = User::where('sap_seller_id','>','0')->pluck('name','sap_seller_id');
		$arrayData[] = ['物料号','站点','日期','产品状态','销售员ID','BG','BU','采购单价','体积','尺寸','关税单价','头程运费单价','单位仓储费','人工成本','订单金额','销量','操作费','交易费','其他费用','退货数量','Deal营销费','Coupon营销费','CPC营销费','FBM库存','FBM仓储费','FBA库存','FBA仓储费','库存金额','资金占用成本','经济效益','业务净利润','保留品基数','淘汰品基数1','淘汰品基数2','销量目标','销量完成率','销售额目标','销售额完成率','利润目标','利润完成率','提成基数'];
		foreach($daily_info as $val){
			$arrayData[] = [
				array_get($val,'sku'),
				array_get($val,'site'),
				array_get($val,'date'),
				array_get($type_set,array_get($val,'status'),array_get($val,'status')),
				array_get($seller_set,array_get($val,'sap_seller_id'),array_get($val,'sap_seller_id')),
				array_get($val,'bg'),
				array_get($val,'bu'),
				array_get($val,'cost'),
				array_get($val,'volume'),
				array_get($size_set,array_get($val,'size'),array_get($val,'size')),
				array_get($val,'tax'),
				array_get($val,'headshipfee'),
				array_get($val,'unit_storage'),
				array_get($val,'cost_set'),
				array_get($val,'amount'),
				array_get($val,'sales'),
				array_get($val,'fulfillmentfee'),
				array_get($val,'commission'),
				array_get($val,'otherfee'),
				array_get($val,'returnqty'),
				array_get($val,'deal'),
				array_get($val,'coupon'),
				array_get($val,'cpc'),
				array_get($val,'fbm_stock'),
				array_get($val,'fbm_storage'),
				array_get($val,'fba_stock'),
				array_get($val,'fba_storage'),
				
				array_get($val,'stock_amount'),
				
				array_get($val,'amount_used'),
				array_get($val,'economic'),
				array_get($val,'profit'),
				array_get($val,'reserved'),
				array_get($val,'eliminate1'),
				array_get($val,'eliminate2'),
				array_get($val,'sales_target'),
				array_get($val,'sales_per'),
				array_get($val,'amount_target'),
				array_get($val,'amount_per'),
				array_get($val,'profit_target'),
				array_get($val,'profit_per'),
				array_get($val,'bonus')
			];
		}
		$spreadsheet->getSheet(1)->fromArray($arrayData,NULL,'A1');
		
		$spreadsheet->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
		header('Content-Disposition: attachment;filename="Export_'.array_get($_REQUEST,'ExportType').'.xlsx"');//告诉浏览器输出浏览器名称
		header('Cache-Control: max-age=0');//禁止缓存
		$writer = new Xlsx($spreadsheet);
		$writer->save('php://output');
	
	}

}