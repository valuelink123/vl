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
		$level = $request->get('level');
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
		
		if($level){
			$where.= " and a.pro_status = '".$level."'";
		}
		if($sku){
			$where.= " and (a.asin='".$sku."' or a.item_code='".$sku."')";
		}
		
		
		$sql = "(select a.asin,a.site,a.item_code,a.status,a.pro_status,a.bg,a.bu,a.sap_seller_id,
		b.item_name
from (select asin,site,max(item_no) as item_code,max(item_status) as status,max(status) as pro_status, max(bg) as bg,max(bu) as bu,max(sap_seller_id) as sap_seller_id from asin group by asin,site) as a
left join fbm_stock as b on a.item_code =b.item_code
 ".$where." order by a.item_code asc ) as sku_tmp_cc";
 		$datas = DB::table(DB::raw($sql))->paginate(5);
		$date_arr=$asin_site_arr=$sku_site_arr=$datas_details=$oa_data=[];
		$site_code['www_amazon_com']='US';
		$site_code['www_amazon_ca']='CA';
		$site_code['www_amazon_de']='DE';
		$site_code['www_amazon_it']='IT';
		$site_code['www_amazon_es']='ES';
		$site_code['www_amazon_co_uk']='UK';
		$site_code['www_amazon_fr']='FR';
		$site_code['www_amazon_co_jp']='JP';
		foreach($datas as $data){
			$asin_site_arr[] = "(asin = '".$data->asin."' and site='".$data->site."')";
			$sku_site_arr[] = "(SKU = '".$data->item_code."' and zhand='".array_get($site_code,str_replace('.','_',$data->site),'')."')";
 		}
		for($i=7;$i>=0;$i--){
			$date_arr[]=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
		}
		if($asin_site_arr){
			$datas_item=Skusweekdetails::whereIn('weeks',$date_arr)->whereRaw('('.implode(' or ',$asin_site_arr).')')->get()->toArray();
			
			foreach($datas_item as $di){
				$datas_details[str_replace('.','',$di['site']).'-'.$di['asin'].'-'.$di['weeks']] = $di;
			}
			
			$oa_datas = [];//DB::connection('oa')->table('formtable_main_193_dt1')->whereRaw('('.implode(' or ',$sku_site_arr).')')->get();
			$oa_datas=json_decode(json_encode($oa_datas), true);
			foreach($oa_datas as $od){
				$oa_data[$od['zhand'].'-'.$od['SKU']] = $od;
			}
		}

        $returnDate['teams']= DB::select('select bg,bu from asin group by bg,bu ORDER BY BG ASC,BU ASC');
		$returnDate['users']= $this->getUsers();
		$returnDate['date_start']= $date_start;
		$returnDate['s_user_id']= $user_id?$user_id:[];
		$returnDate['bgbu']= $bgbu;
		$returnDate['week']= $week;
		$returnDate['s_level']= $level;
		$returnDate['sku']= $sku;
		$returnDate['s_site']= $site;
		$returnDate['site_code']= $site_code;
		$returnDate['datas']= $datas;
		$returnDate['datas_details']= $datas_details;
		$returnDate['oa_data']= $oa_data;
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
			$ex = Skusweekdetails::where('asin',array_get($data,1))->where('site',array_get($data,0))->where('weeks', array_get($data,2))->first()->toArray();
			$return[str_replace('.','',array_get($data,0)).'-'.array_get($data,1).'-'.array_get($data,2).'-total_stock']=intval($ex['fba_stock']+$ex['fbm_stock']+$ex['fba_transfer']);
			$return[str_replace('.','',array_get($data,0)).'-'.array_get($data,1).'-'.array_get($data,2).'-fba_keep']=($ex['sales'])?round(intval($ex['fba_stock'])/$ex['sales'],2):'∞';
			$return[str_replace('.','',array_get($data,0)).'-'.array_get($data,1).'-'.array_get($data,2).'-total_keep']=($ex['sales'])?round((intval($ex['fba_stock'])+intval($ex['fba_transfer'])+intval($ex['fbm_stock']))/$ex['sales'],2):'∞';
			 echo json_encode($return);
			
		}
				
    }



}