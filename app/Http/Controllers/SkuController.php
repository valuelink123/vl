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
		$where='';
		$site = $request->get('site');
		$bgbu = $request->get('bgbu');
		$user_id = $request->get('user_id');
		$sku = $request->get('sku');
		if(Auth::user()->sap_seller_id) $where.= "and a.sap_seller_id=".Auth::user()->sap_seller_id;
		if($bgbu){
		   $bgbu_arr = explode('_',$bgbu);
		   if(array_get($bgbu_arr,0)){
				$where.= "and a.bg='".array_get($bgbu_arr,0)."'";
		   }
		   if(array_get($bgbu_arr,1)){
				$where.= "and a.bu='".array_get($bgbu_arr,1)."'";
		   }
		}
		if($site){
			$where.= "and a.site='".$site."'";
		}
		if($user_id){
			$where.= "and a.sap_seller_id=".$user_id;
		}
		if($sku){
			$where.= "and (a.asin='".$sku."' or a.item_code='".$sku."')";
		}
	    $date_start = $request->get('date_start')?$request->get('date_start'):date('Y-m-d',strtotime('+ 8hours'));
		$week = self::getWeek($date_start);
		$datas = DB::select("select a.asin,a.site,a.item_code,a.status,a.bg,a.bu,a.sap_seller_id,
		b.item_name,c.keywords,c.fba_stock,c.fbm_stock,c.fba_transfer,c.total_stock,c.fba_keep,c.total_keep,c.strategy,
		d.ranking,d.rating,d.review,d.sales,d.price,d.flow,d.conversion

from (select asin,site,max(item_no) as item_code,max(item_status) as status, max(bg) as bg,max(bu) as bu,max(sap_seller_id) as sap_seller_id from asin where status in ('A','B') group by asin,site) as a
left join fbm_stock as b on a.item_code =b.item_code
left join skus_week as c on a.asin = c.asin and a.site=c.site
left join skus_week_details as d on a.asin = d.asin and a.site=d.site and d.weeks = '".$week."'
where 1=1 ".$where);


        $returnDate['teams']= DB::select('select bg,bu from asin group by bg,bu ORDER BY BG ASC,BU ASC');
		$returnDate['users']= $this->getUsers();
		$returnDate['date_start']= $date_start;
		$returnDate['s_user_id']= $user_id;
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
		
        $data = json_decode($request->get('data'));
		$redata['result']=0;
		if(count($data)==57){
			$redata['result']=1;
			$week = self::getWeek(trim($data[2]));
			$asin = strip_tags(trim($data[0]));
			$site = trim($data[1]);
			
			$p_data=$i_data=$cus_arr=[];
			$p_data['keywords'] = trim($data[3]);
			for($i=4;$i<11;$i++){
				$cus_arr[]=trim($data[$i]);
			}
			$i_data['ranking'] = implode(';',$cus_arr);
			
			$cus_arr=[];
			for($i=11;$i<18;$i++){
				$cus_arr[]=trim($data[$i]);
			}
			$i_data['rating'] = implode(';',$cus_arr);
			
			$cus_arr=[];
			for($i=18;$i<25;$i++){
				$cus_arr[]=trim($data[$i]);
			}
			$i_data['review'] = implode(';',$cus_arr);
			
			$cus_arr=[];
			$sales_d=$sales_t=0;
			for($i=25;$i<32;$i++){
				if(is_numeric(trim($data[$i]))){
					$cus_arr[]=round(trim($data[$i]),2);
					$sales_t+=round(trim($data[$i]),2);
					$sales_d++;
					
				}else{
					$cus_arr[]='';
					
				}
			}
			$i_data['sales'] = implode(';',$cus_arr);
			$daily_sales=($sales_d>0)?round($sales_t/$sales_d,2):0;
			$cus_arr=[];
			for($i=32;$i<39;$i++){
				$cus_arr[]=trim($data[$i]);
			}
			$i_data['price'] = implode(';',$cus_arr);
			
			$cus_arr=[];
			for($i=39;$i<46;$i++){
				$cus_arr[]=trim($data[$i]);
			}
			$i_data['flow'] = implode(';',$cus_arr);
			
			$cus_arr=[];
			for($i=46;$i<53;$i++){
				$cus_arr[]=trim($data[$i]);
			}
			$i_data['conversion'] = implode(';',$cus_arr);
			$p_data['weeks']=$week;
			$p_data['fba_stock']=intval(trim($data[53]));
			$p_data['fba_transfer']=intval(trim($data[54]));
			$p_data['fbm_stock']=intval(trim($data[55]));
			$p_data['total_stock']=$p_data['fba_stock']+$p_data['fba_transfer']+$p_data['fbm_stock'];
			$p_data['strategy']=trim($data[56]);
			$p_data['fba_keep']=($daily_sales>0)?(round(($p_data['fba_stock'])/$daily_sales,2)):(($p_data['fba_stock'])*100);
			$p_data['total_keep']=($daily_sales>0)?(round($p_data['total_stock']/$daily_sales,2)):($p_data['total_stock']*100);
			
			$exists_week = Skusweek::where('asin',$asin)->where('site',$site)->value('weeks');
			//var_dump($p_data);
			if($week>=$exists_week){
				Skusweek::updateOrCreate([
					'asin' => $asin,
					'site' => $site],$p_data);
					
				$redata['total_stock']=$p_data['total_stock'];
				$redata['fba_keep']=$p_data['fba_keep'];
				$redata['total_keep']=$p_data['total_keep'];
			}
			Skusweekdetails::updateOrCreate([
					'asin' => $asin,
					'site' => $site,
					'weeks' => $week],$i_data);
			
			
			
		}
		die(json_encode($redata));
    }



}