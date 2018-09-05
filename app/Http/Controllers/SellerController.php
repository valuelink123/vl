<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sellertab;
use Illuminate\Support\Facades\Session;
use App\Groupdetail;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use Log;
class SellerController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tabs = Sellertab::get();
		$seller_tabs= array();
		
		$config_fields = getFieldtoField();
		$addwhere = '';
		if(!Auth::user()->admin) $addwhere = " and seller in ('".implode("','", $this->getUser())."')";
		foreach($tabs as $tab){
			$seller_tabs[$tab->id]['tab']=$tab->tab;
			$rule = unserialize($tab->tab_rules);
			
			$order = array_get($config_fields,array_get($rule,'order'));
			$by = array_get($rule,'by');
			$tab_rules= array_get($rule,'tabrules');
			$show_rules= array_get($rule,'showrules');
			$where = 'where b.asin is not null';
			
			$sr_arr = [];
			foreach($show_rules as $sr){
				$rsr = $sr;
				unset($rsr['action']);unset($rsr['color']);
				$sr_arr[array_get($sr,'color')]['rules'][]=$rsr;
				if(array_get($sr,'action')) $sr_arr[array_get($sr,'color')]['rec_str']=array_get($sr,'action');
				if(array_get($sr,'explain')) $sr_arr[array_get($sr,'color')]['rec_exp']=array_get($sr,'explain');
			}
			$seller_tabs[$tab->id]['rules']=$sr_arr;
			$seller_tabs[$tab->id]['order']=$order;
			$seller_tabs[$tab->id]['by']=$by;

			foreach($tab_rules as $tr){
				$where = $where.' and '.array_get($config_fields,array_get($tr,'fields')).array_get($tr,'symbols').array_get($tr,'value');
			}
			
			
			
			$showdata = DB::select("select a.seller,b.* from seller_asins as b left join (select site,seller,asin from asin group by site,seller,asin) as a on a.asin=b.asin and a.site=b.site $where $addwhere order by $order $by");
		$data=[];
		$showdata = json_decode(json_encode($showdata),TRUE);
		foreach($showdata as $data_s){
			$color='';
			foreach($sr_arr as $key=>$rule_arr){
				
				$matched=false;
				foreach(array_get($rule_arr,'rules') as $rule){
					
					
					$field_value = round(array_get($data_s,array_get($config_fields,array_get($rule,'fields'))),2);
					$symbol = trim(array_get($rule,'symbols'));
					$value = round(array_get($rule,'value'),2);

					switch ($symbol)
					{
					case '>=':
					  $matched=($field_value>=$value)?true:false;
					  break;
					case '>':
					  $matched=($field_value>$value)?true:false;
					  break;
					case '=':
					  $matched=($field_value==$value)?true:false;
					  break;
					case '<=':
					  $matched=($field_value<=$value)?true:false;
					  break;
					case '<':
					  $matched=($field_value<$value)?true:false;
					  break;
					default:
					  $matched=false;
					}

					if(!$matched) break;
				}
				if($matched){
					$color=$key;
				}
				
			}
			$data_s['color']=$color;
			$data[]=$data_s;

		}
			$seller_tabs[$tab->id]['data'] = $data;
		}
		//echo "<pre>";
		//print_r($seller_tabs);
		//die();
		$positives=DB::select("select a.seller,b.* from seller_asins as b left join (select site,seller,asin from asin group by site,seller,asin) as a on a.asin=b.asin and a.site=b.site where positive_value>0 $addwhere order by positive_value desc");
		$negatives=DB::select("select a.seller,b.* from review as b left join (select site,seller,asin from asin group by site,seller,asin) as a on a.asin=b.asin and a.site=b.site where negative_value>0 and status=1 $addwhere order by negative_value desc");
		
        return view('seller/index',['tabs'=>$seller_tabs,'positives'=>json_decode(json_encode($positives),TRUE),'negatives'=>json_decode(json_encode($negatives),TRUE)]);

    }

    public function getUser(){
		$users_arr = array();
		if(Auth::user()->admin){
            $users = User::all();
			
			foreach($users as $user){
				$users_arr[] = $user->name;
			}
			return $users_arr;
        }else{
			$user_id = Auth::user()->id;
            $groups = Groupdetail::where('user_id',$user_id)->where('leader',1)->get(['group_id']);
			$group_arr =array();
			foreach($groups as $group){
				$group_arr[] = $group->group_id;
			}
			$user_ids = Groupdetail::whereIn('group_id',$group_arr)->get(['user_id']);
			$user_ids_arr = array();
			$user_ids_arr[] = $user_id;
			foreach($user_ids as $user){
				$user_ids_arr[] = $user->user_id;
			}
			$users = User::whereIn('id',$user_ids_arr)->get();
			foreach($users as $user){
				$users_arr[] = $user->name;
			}
			return $users_arr;
        }
	}
	
	public function getrating(Request $request){
		$site = $request->get('site');
		$asin = $request->get('asin');
		$review_arr[1]=$review_arr[2]=$review_arr[3]=[];
		$reviewList = DB::select("select asin,site,date,review,rating,review_content,reviewer_name from review where asin='".$asin."' and site='".$site."' and date>'2015-01-01' and `status` in (1,2,7,8)");
		foreach($reviewList as $review){
			$review->review_content=strip_tags($review->review_content);
			$review_arr[intval($review->rating)][$review->review]=$review;
		}
		die(json_encode($review_arr));
	}
	
	public function show($asin,$marketplaceid)
    {	
		$site = 'www.'.array_get(getSiteUrl(),$marketplaceid);
		$data_list = DB::select("select * from seller_asins where asin='".$asin."' and marketplaceid='".$marketplaceid."'");
		$data = $data_list[0];
		$date_from = date('Y-m-d',strtotime('-15 day')).' 00:00:00';
		$date_to = date('Y-m-d',strtotime('-1 day')).' 23:59:59';
		/*
		$sales = DB::connection('order')->select('select sum(quantityordered) as sale,asin from amazon_orders_item where asin=:asin and exists ( select AmazonOrderId from
(select SellerId,AmazonOrderId from amazon_orders where PurchaseDate>=:date_from and PurchaseDate<=:date_to) as a 
where  a.SellerId=amazon_orders_item.SellerId and a.AmazonOrderId=amazon_orders_item.AmazonOrderId) group by asin',['date_from' => $date_from,'date_to' => $date_to,'asin'=>$asin]);

		if($sales)	$data->total_sales = intval($sales[0]->sale);
		*/
		
		$stars = DB::select('select * from star where asin=:asin and domain=:site',['asin'=>$asin,'site'=>$site]);

		if($stars) $data->stars_details = $stars;
		
		
		if($data->item_code){
			$item_code_arr=unserialize($data->item_code);
			$item_code = "('".implode("','", $item_code_arr)."')";
			$dates = date('Ymd',strtotime('-30 day'));
			$profits = DB::select('select sales_profits,income,item_code,date,seller_name from asin_profits where date>=:dates and item_code in '.$item_code,['dates' => $dates]);
			if($profits) $data->in_profits= $profits;
			
			
			
			$ads = DB::select('select cost_base as cost,item_code,date_start as date from asin_ads where date_start>=:dates and item_code in '.$item_code,['dates' => $dates]);
			
			if($ads) $data->ads= $ads;
			
			
			$fbm_stock = DB::select('select item_code,item_name,cost,fbm_stock,fbm_amount,updated_at as date from fbm_stock where item_code in '.$item_code);
			
			if($fbm_stock) $data->fbm_stock_data= $fbm_stock;
			$eu = array('A1F83G8C2ARO7P','A1PA6795UKMFR9','APJ6JRA9NG5V4','A1RKKUPIHCS9HS','A13V1IB3VIYZZH');
			$us = array('A2EUQ1WTGCTBG2','A1AM78C64UM0Y8','ATVPDKIKX0DER');
			$jp = array('A1VC38T7YXB528');
			$str =[];
			if(in_array($data->marketplaceid,$eu)) $str=$eu;
			if(in_array($data->marketplaceid,$us)) $str=$us;
			if(in_array($data->marketplaceid,$jp)) $str=$jp;
			$str = "('".implode("','", $str)."')";
			$fba_stock = DB::connection('order')->select('select sellerid,asin,sellersku,instock,(total-instock) as transfer,total,updated_at as date from amazon_inventory_supply where total>0 and sellerid in (select sellerid from accounts where marketplaceid in '.$str.') and asin=:asins',['asins' => $asin]);
			
			if($fba_stock) $data->fba_stock_data= $fba_stock;
		}

		return view('seller/view',['data'=>$data]);
	}




}