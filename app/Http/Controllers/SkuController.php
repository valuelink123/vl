<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Skusweek;
use App\Skusweekdetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use DB;


class SkuController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     *
     */

    public function __construct()
    {
        $this->middleware('auth');
    }


	
    public function index(Request $request)
    {
		
		$site = $request->get('site');
		$bgbu = $request->get('bgbu');
		$user_id = $request->get('user_id');
		$sku = $request->get('sku');
		
	    $date_start = $request->get('date_start')?$request->get('date_start'):date('Y-m-d',strtotime('+ 8hours'));
		$week = self::getWeek($date_start);

		
		
		if (Auth::user()->seller_rules) {
			$rules = explode("-",Auth::user()->seller_rules);
			$where= "where 1=1";
			if(array_get($rules,0)!='*') $where.= " and a.bg='".array_get($rules,0)."'";
			if(array_get($rules,1)!='*') $where.= " and a.bu='".array_get($rules,1)."'";
		} elseif (Auth::user()->sap_seller_id) {
			$where= "where a.sap_seller_id=".Auth::user()->sap_seller_id;
		} else {
			$where= "where 1=1";
		}
		if($bgbu){
		   $bgbu_arr = explode('_',$bgbu);
		   if(array_get($bgbu_arr,0)){
				$where.= " and a.bg='".array_get($bgbu_arr,0)."'";
		   }
		   if(array_get($bgbu_arr,1)){
				$where.= " and a.bu='".array_get($bgbu_arr,1)."'";
		   }
		}
		if($site){
			$where.= " and a.site='".$site."'";
		}
		if($user_id){
			$where.= " and a.sap_seller_id in (".implode(',',$user_id).")";
		}
		if($sku){
			$where.= " and (a.asin='".$sku."' or a.item_code='".$sku."')";
		}
		
		
		$sql = "(select a.asin,a.site,a.item_code,a.status,a.pro_status,a.bg,a.bu,a.sap_seller_id,
		b.item_name,d.keywords,d.strategy,
		d.ranking_0,d.rating_0,d.review_0,d.sales_0,d.price_0,d.flow_0,d.conversion_0,d.fba_stock_0,d.fbm_stock_0,d.fba_transfer_0,
		d.ranking_1,d.rating_1,d.review_1,d.sales_1,d.price_1,d.flow_1,d.conversion_1,d.fba_stock_1,d.fbm_stock_1,d.fba_transfer_1,
		d.ranking_2,d.rating_2,d.review_2,d.sales_2,d.price_2,d.flow_2,d.conversion_2,d.fba_stock_2,d.fbm_stock_2,d.fba_transfer_2,
		d.ranking_3,d.rating_3,d.review_3,d.sales_3,d.price_3,d.flow_3,d.conversion_3,d.fba_stock_3,d.fbm_stock_3,d.fba_transfer_3,
		d.ranking_4,d.rating_4,d.review_4,d.sales_4,d.price_4,d.flow_4,d.conversion_4,d.fba_stock_4,d.fbm_stock_4,d.fba_transfer_4,
		d.ranking_5,d.rating_5,d.review_5,d.sales_5,d.price_5,d.flow_5,d.conversion_5,d.fba_stock_5,d.fbm_stock_5,d.fba_transfer_5,
		d.ranking_6,d.rating_6,d.review_6,d.sales_6,d.price_6,d.flow_6,d.conversion_6,d.fba_stock_6,d.fbm_stock_6,d.fba_transfer_6

from (select asin,site,max(item_no) as item_code,max(item_status) as status,max(status) as pro_status, max(bg) as bg,max(bu) as bu,max(sap_seller_id) as sap_seller_id from asin group by asin,site) as a
left join fbm_stock as b on a.item_code =b.item_code
left join skus_week_details as d on a.asin = d.asin and a.site=d.site and d.weeks = '".$week."'
 ".$where." order by a.item_code asc ) as sku_tmp_cc";
 		$datas = DB::table(DB::raw($sql))->paginate(5);


        $returnDate['teams']= DB::select('select bg,bu from asin group by bg,bu ORDER BY BG ASC,BU ASC');
		$returnDate['users']= $this->getUsers();
		$returnDate['date_start']= $date_start;
		$returnDate['s_user_id']= $user_id?$user_id:[];
		$returnDate['bgbu']= $bgbu;
		$returnDate['week']= $week;
		$returnDate['sku']= $sku;
		$returnDate['s_site']= $site;
		$returnDate['datas']= $datas;
        return view('sku/index',$returnDate);

    }

    public function getUsers(){
        $users = User::where('sap_seller_id','>',0)->get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['sap_seller_id']] = $user['name'];
        }
        return $users_array;
    }
	public function getWeek($date_start){
		$week = date('YW',strtotime($date_start));
		if(date('m',strtotime($date_start))==1 && date('W',strtotime($date_start))>50) $week = (date('Y',strtotime($date_start))-1).date('W',strtotime($date_start));
		if(date('m',strtotime($date_start))==12 && date('W',strtotime($date_start))<2) $week = (date('Y',strtotime($date_start))+1).date('W',strtotime($date_start));
		return $week;
	}

    public function update(Request $request)
    {
		
		$name = $request->get('name');
		$data = explode("-",$name);
		Skusweekdetails::updateOrCreate([
				'asin' => array_get($data,1),
				'site' => array_get($data,0),
				'weeks' => array_get($data,2)],[array_get($data,3)=>$request->get('value')]);
		if(in_array(strtoupper(substr(array_get($data,3),0,2)),array('SA','FB'))){
			$pos = substr(array_get($data,3),-1);
			$ex = Skusweekdetails::where('asin',array_get($data,1))->where('site',array_get($data,0))->where('weeks', array_get($data,2))->first()->toArray();
			$return[str_replace('.','',array_get($data,0)).'-'.array_get($data,1).'-'.array_get($data,2).'-total_stock_'.$pos]=intval($ex['fba_stock_'.$pos]+$ex['fbm_stock_'.$pos]+$ex['fba_transfer_'.$pos]);
			$return[str_replace('.','',array_get($data,0)).'-'.array_get($data,1).'-'.array_get($data,2).'-fba_keep_'.$pos]=($ex['sales_'.$pos])?round(intval($ex['fba_stock_'.$pos])/$ex['sales_'.$pos],2):'∞';
			$return[str_replace('.','',array_get($data,0)).'-'.array_get($data,1).'-'.array_get($data,2).'-total_keep_'.$pos]=($ex['sales_'.$pos])?round((intval($ex['fba_stock_'.$pos])+intval($ex['fba_transfer_'.$pos])+intval($ex['fbm_stock_'.$pos]))/$ex['sales_'.$pos],2):'∞';
			 echo json_encode($return);
			
		}
				
    }



}