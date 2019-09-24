<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Groupdetail;
use App\User;
use App\Task;
use App\Asin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use log;

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

    }	

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$limit_bg = $limit_bu = $limit_sap_seller_id = $limit_review_user_id='';
		
		$sumwhere = 'length(asin)=10';
		if (Auth::user()->seller_rules) {
			$rules = explode("-", Auth::user()->seller_rules);
			if (array_get($rules, 0) != '*') $limit_bg = array_get($rules, 0);
			if (array_get($rules, 1) != '*') $limit_bu = array_get($rules, 1);
		} elseif (Auth::user()->sap_seller_id) {
			$limit_sap_seller_id = Auth::user()->sap_seller_id;
		} else {
			$limit_review_user_id = Auth::user()->id;
		}
			
		
		$asins = Asin::whereRaw('length(asin)=10')->orderByRaw("case when status = 'S' Then '0' else status end asc,sales_07_01 desc")
		->leftJoin( DB::raw("(select asin as asin_b,domain,avg(sessions) as sessions,avg(unit_session_percentage) as unit_session_percentage,avg(bsr) as bsr from star_history where create_at >= '".date('Y-m-d',strtotime('-10day'))."' group by asin,domain) as asin_star") ,function($q){
			$q->on('asin.asin', '=', 'asin_star.asin_b')
				->on('asin.site', '=', 'asin_star.domain');
		});
		
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
		
		
		if(array_get($_REQUEST,'user_id')){
			$sap_seller_id = User::where('id',array_get($_REQUEST,'user_id'))->value('sap_seller_id');
			$select_user_id=array_get($_REQUEST,'user_id');
			$asins = $asins->where(function ($query) use ($sap_seller_id,$select_user_id) {
				$query->where('asin.sap_seller_id', $sap_seller_id)
				->orwhere('asin.review_user_id', $select_user_id);
			});
			$sumwhere.=" and (sap_seller_id='$sap_seller_id' or review_user_id='$select_user_id')";
		}
		
		
		$fba_stock_info = DB::select("select sum(fba_stock*fba_cost) as fba_total_amount,sum(fba_stock) as fba_total_stock from (select avg(fba_stock) as fba_stock,avg(fba_cost) as fba_cost from asin where $sumwhere group by asin,sellersku ) as fba_total");
		
		$fbm_stock_info = DB::select("select sum(fbm_stock*fbm_cost) as fbm_total_amount,sum(fbm_stock) as fbm_total_stock from (select avg(fbm_stock) as fbm_stock,avg(fbm_cost) as fbm_cost from asin where $sumwhere group by item_no) as fbm_total");
		
		$asins = $asins->take(5)->get()->toArray();
		
		$date_from = $request->get('date_from')?$request->get('date_from'):date('Y-m').'-01';
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d');
		
		
		
		
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
        $users = User::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }

}