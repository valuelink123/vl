<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Skusweekdetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
		parent::__construct();
    }


	
    public function index(Request $request)
    {
		if(!Auth::user()->can(['sales-report-show'])) die('Permission denied -- sales-report-show');
		$site = $request->get('site');
		$bgbu = $request->get('bgbu');
		$user_id = $request->get('user_id');
		$sku = $request->get('sku');
		$level = $request->get('level');
	    $date_start = $request->get('date_start')?$request->get('date_start'):date('Y-m-d',strtotime('-14 days'));
		$date_end = $request->get('date_end')?$request->get('date_end'):date('Y-m-d');
		if($date_end<$date_start) $date_end = $date_start;

		$where= "where length(trim(asin))=10";
		
		if (Auth::user()->seller_rules) {
			$rules = explode("-",Auth::user()->seller_rules);
			if(array_get($rules,0)!='*') $where.= " and bg='".array_get($rules,0)."'";
			if(array_get($rules,1)!='*') $where.= " and bu='".array_get($rules,1)."'";
		} elseif (Auth::user()->sap_seller_id) {
			$where.= " and sap_seller_id=".Auth::user()->sap_seller_id;
		} else {
		
		}
		if($bgbu){
		   $bgbu_arr = explode('_',$bgbu);
		   if(array_get($bgbu_arr,0)){
				$where.= " and bg='".array_get($bgbu_arr,0)."'";
		   }
		   if(array_get($bgbu_arr,1)){
				$where.= " and bu='".array_get($bgbu_arr,1)."'";
		   }
		}
		if($site){
			$where.= " and marketplace_id='".$site."'";
		}
		if($user_id){
			$where.= " and sap_seller_id in (".implode(',',$user_id).")";
		}
		
		if($level){
			$where.= " and pro_status = '".(($level=='S')?0:$level)."'";
		}
		$where_add='1=1';
		if($sku){
			$where_add = " (asin='".$sku."' or sku like '%".$sku."%' or description like '%".$sku."%')";
		}
		
		
		$sql = "(select * from (select asin,marketplace_id,group_concat(a.sku) as sku,group_concat(b.description) as description,any_value(sku_status) as status,
any_value(status) as pro_status, any_value(bg) as bg,any_value(bu) as bu,any_value(sap_seller_id) as sap_seller_id
from 

(select asin,marketplace_id,sku,any_value(sku_status) as sku_status,
any_value(case when status = 'S' Then '0' else status end) as status, 
any_value(sap_seller_bg) as bg,any_value(sap_seller_bu) as bu,any_value(sap_seller_id) as sap_seller_id from sap_asin_match_sku group by asin,marketplace_id,sku)

 as a left join sap_skus as b on a.sku=b.sku group by asin,marketplace_id) as c $where order by pro_status asc) as sku_tmp_cc";
 		$datas = DB::connection('amazon')->table(DB::raw($sql))->whereRaw($where_add)->paginate(5);
		$site_code['www_amazon_com']='US';
		$site_code['www_amazon_ca']='CA';
		$site_code['www_amazon_de']='DE';
		$site_code['www_amazon_it']='IT';
		$site_code['www_amazon_es']='ES';
		$site_code['www_amazon_co_uk']='UK';
		$site_code['www_amazon_fr']='FR';
		$site_code['www_amazon_co_jp']='JP';
		foreach($datas as $data){
			$data->last_keywords=Skusweekdetails::where('asin',$data->asin)->where('marketplace_id',$data->marketplace_id)->whereNotNull('keywords')->orderBy('date','desc')->take(1)->value('keywords');	
			$data->details=Skusweekdetails::where('date','>=',$date_start)->where('date','<=',$date_end)->where('asin',$data->asin)->where('marketplace_id',$data->marketplace_id)->get()->keyBy('date')->toArray();
			
 		}
        $returnDate['teams']= getUsers('sap_bgbu');
		$returnDate['users']= getUsers('sap_seller');
		$returnDate['date_start']= $date_start;
		$returnDate['date_end']= $date_end;
		$returnDate['s_user_id']= $user_id?$user_id:[];
		$returnDate['bgbu']= $bgbu;
		$returnDate['s_level']= $level;
		$returnDate['sku']= $sku;
		$returnDate['s_site']= $site;
		$returnDate['site_code']= $site_code;
		$returnDate['datas']= $datas;
        return view('sku/index',$returnDate);

    }


	public function export(Request $request){
		if(!Auth::user()->can(['sales-report-export'])) die('Permission denied -- sales-report-export');
		set_time_limit(0);
		$site = $request->get('site');
		$bgbu = $request->get('bgbu');
		$user_id = $request->get('user_id');
		$sku = $request->get('sku');
		$level = $request->get('level');
	    $date_start = $request->get('date_start')?date('Y-m-d',strtotime($request->get('date_start'))):date('Ymd',strtotime('-14 days'));
		$date_end = $request->get('date_end')?date('Y-m-d',strtotime($request->get('date_end'))):date('Ymd');
		if($date_end<$date_start) $date_end = $date_start;

		
		$where= " length(trim(asin))=10 and date>='$date_start' and date<='$date_end' ";
		if (Auth::user()->seller_rules) {
			$rules = explode("-",Auth::user()->seller_rules);
			if(array_get($rules,0)!='*') $where.= " and bg='".array_get($rules,0)."'";
			if(array_get($rules,1)!='*') $where.= " and bu='".array_get($rules,1)."'";
		} elseif (Auth::user()->sap_seller_id) {
			$where.= "sap_seller_id=".Auth::user()->sap_seller_id;
		} else {
			
		}
		if($bgbu){
		   $bgbu_arr = explode('_',$bgbu);
		   if(array_get($bgbu_arr,0)){
				$where.= " and bg='".array_get($bgbu_arr,0)."'";
		   }
		   if(array_get($bgbu_arr,1)){
				$where.= " and bu='".array_get($bgbu_arr,1)."'";
		   }
		}
		if($site){
			$where.= " and marketplace_id='".$site."'";
		}
		if($user_id){
			$where.= " and sap_seller_id in (".$user_id.")";
		}
		
		if($level){
			$where.= " and sku_tmp_cc.pro_status = '".(($level=='S')?0:$level)."'";
		}
		
		if($sku){
			$where.= " and (asin='".$sku."' or sku_tmp_cc.item_code  like '%".$sku."%' or sku_tmp_cc.item_name like '%".$sku."%')";
		}
		
			
		$month = date('Ym',strtotime($date_start));
        $datas=Skusweekdetails::whereRaw($where)
			->leftJoin(DB::raw("(
select asin as asin_p,marketplace_id as marketplace_id_p,group_concat(a.sku) as sku,group_concat(b.description) as description,any_value(sku_status) as status,
any_value(status) as pro_status, any_value(bg) as bg,any_value(bu) as bu,any_value(sap_seller_id) as sap_seller_id
from 

(select asin,marketplace_id,sku,any_value(sku_status) as sku_status,
any_value(case when status = 'S' Then '0' else status end) as status, 
any_value(sap_seller_bg) as bg,any_value(sap_seller_bu) as bu,any_value(sap_seller_id) as sap_seller_id from sap_asin_match_sku group by asin,marketplace_id,sku)

 as a left join sap_skus as b on a.sku=b.sku group by asin,marketplace_id ) as sku_tmp_cc"),function($q){
				$q->on('asin_daily_report.asin', '=', 'sku_tmp_cc.asin_p')
				  ->on('asin_daily_report.marketplace_id', '=', 'sku_tmp_cc.marketplace_id_p');
			})
		->orderBy('asin','asc')->orderBy('marketplace_id','asc')->orderBy('date','asc')->get()->toArray();
		
		
		$arrayData = array();
		$headArray[] = 'Asin';
		$headArray[] = 'Site';
		$headArray[] = 'Sku';
		$headArray[] = 'Seller';
		$headArray[] = 'BG';
		$headArray[] = 'BU';
		$headArray[] = 'Status';
		$headArray[] = 'Level';
		$headArray[] = 'Description';
		$headArray[] = 'Date';
		$headArray[] = 'Main Keywords';
		$headArray[] = 'Ranking';
		$headArray[] = 'Rating';
		$headArray[] = 'Review';
		$headArray[] = 'Sales';
		$headArray[] = 'Price';
		$headArray[] = 'Session';
		$headArray[] = 'Conversion';
		$headArray[] = 'FBA Stock';
		$headArray[] = 'FBA Transfer';
		$headArray[] = 'FBM Stock';
		$headArray[] = 'Total Stock';
		$headArray[] = 'FBA Keep';
		$headArray[] = 'Total Keep';
		$headArray[] = 'Strategy';

		$arrayData[] = $headArray;
		$users_array = getUsers('sap_seller');;
		
		foreach($datas as $data){
			
			
			
            $arrayData[] = array(
               	$data['asin'],
				array_get(getSiteUrl(),$data['marketplace_id']),
				$data['sku'],
				array_get($users_array,intval(array_get($data,'sap_seller_id')),intval(array_get($data,'sap_seller_id'))),
				$data['bg'],
				$data['bu'],
				array_get(getSkuStatuses(),$data['status']),
				($data['pro_status']==='0')?'S':$data['pro_status'],
				$data['description'],
				$data['date'],
				$data['keywords'],
				$data['ranking'],
				$data['rating'],
				$data['review'],
				$data['sales'],
				$data['price'],
				$data['flow'],
				$data['conversion'],
				$data['fba_stock'],
				$data['fba_transfer'],
				$data['fbm_stock'],
				intval($data['fba_stock']+$data['fba_transfer']+$data['fbm_stock']),
				($data['sales'])?round(intval($data['fba_stock'])/$data['sales'],2):'∞',
				($data['sales'])?round((intval($data['fba_stock'])+intval($data['fba_transfer'])+intval($data['fbm_stock']))/$data['sales'],2):'∞',
				$data['strategy']
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
			header('Content-Disposition: attachment;filename="Export_d_report.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
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
		if(!Auth::user()->can(['sales-report-update'])) die('Permission denied -- sales-report-update');
		$name = $request->get('name');
		$data = explode(":",$name);
		Skusweekdetails::updateOrCreate([
				'asin' => array_get($data,1),
				'marketplace_id' => array_get($data,0),
				'date' => array_get($data,2)],[array_get($data,3)=>((array_get($data,3)=='conversion')?(($request->get('value'))/100):($request->get('value')))]);
		if(in_array(strtoupper(substr(array_get($data,3),0,2)),array('SA','FB'))){
			$ex = Skusweekdetails::where('asin',array_get($data,1))->where('marketplace_id',array_get($data,0))->where('date', array_get($data,2))->first()->toArray();
			$return[str_replace('.','',array_get($data,0)).':'.array_get($data,1).':'.array_get($data,2).':total_stock']=intval($ex['fba_stock']+$ex['fbm_stock']+$ex['fba_transfer']);
			$return[str_replace('.','',array_get($data,0)).':'.array_get($data,1).':'.array_get($data,2).':fba_keep']=($ex['sales'])?round(intval($ex['fba_stock'])/$ex['sales'],2):'∞';
			$return[str_replace('.','',array_get($data,0)).':'.array_get($data,1).':'.array_get($data,2).':total_keep']=($ex['sales'])?round((intval($ex['fba_stock'])+intval($ex['fba_transfer'])+intval($ex['fbm_stock']))/$ex['sales'],2):'∞';
			 
			
		}else{
			$return[$name]=$request->get('value');
		}
		echo json_encode($return);
				
    }



}