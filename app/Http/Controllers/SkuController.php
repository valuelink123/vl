<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Classes\SapRfcRequest;
use App\User;
use App\Skusweek;
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
    }


	
    public function index(Request $request)
    {
		if(!Auth::user()->can(['sales-report-show'])) die('Permission denied -- sales-report-show');
		$site = $request->get('site');
		$bgbu = $request->get('bgbu');
		$user_id = $request->get('user_id');
		$sku = $request->get('sku');
		$level = $request->get('level');
	    $date_start = $request->get('date_start')?$request->get('date_start'):date('Y-m-d',strtotime('+ 8hours'));
		$week = self::getWeek($date_start);

		$where= "where length(trim(asin))=10";
		
		if (Auth::user()->seller_rules) {
			$rules = explode("-",Auth::user()->seller_rules);
			if(array_get($rules,0)!='*') $where.= " and a.bg='".array_get($rules,0)."'";
			if(array_get($rules,1)!='*') $where.= " and a.bu='".array_get($rules,1)."'";
		} elseif (Auth::user()->sap_seller_id) {
			$where.= " and a.sap_seller_id=".Auth::user()->sap_seller_id;
		} else {
		
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
			$where.= " and a.status = '".(($level=='S')?0:$level)."'";
		}
		$where_add='1=1';
		if($sku){
			$where_add = " (asin='".$sku."' or item_code like '%".$sku."%')";
		}
		
		
		$sql = "(select asin,site,GROUP_CONCAT(a.item_no) as item_code,GROUP_CONCAT(b.item_name) as item_name,max(item_status) as status,
min(status) as pro_status, max(bg) as bg,max(bu) as bu,max(sap_seller_id) as sap_seller_id
from 

(select asin,site,item_no,max(item_status) as item_status,
min(case when status = 'S' Then '0' else status end) as status, 
max(bg) as bg,max(bu) as bu,max(sap_seller_id) as sap_seller_id from asin group by asin,site,item_no)

 as a left join fbm_stock as b on a.item_no=b.item_code $where  group by asin,site order by pro_status asc ) as sku_tmp_cc";
 		$datas = DB::table(DB::raw($sql))->whereRaw($where_add)->paginate(5);
		$date_arr=$asin_site_arr=$datas_details=$oa_data=$sap_data=$last_keywords=[];
		$site_code['www_amazon_com']='US';
		$site_code['www_amazon_ca']='CA';
		$site_code['www_amazon_de']='DE';
		$site_code['www_amazon_it']='IT';
		$site_code['www_amazon_es']='ES';
		$site_code['www_amazon_co_uk']='UK';
		$site_code['www_amazon_fr']='FR';
		$site_code['www_amazon_co_jp']='JP';
		$sap = new SapRfcRequest();
		foreach($datas as $data){
			$sku_site_arr=[];
			$asin_site_arr[] = "(asin = '".$data->asin."' and site='".$data->site."')";
			$match_sku = explode(',',$data->item_code);
			if(count($match_sku)>1){
				$vv001=$vsrhj=$vvvvv=0;
				foreach($match_sku as $k=>$v){
					$sku_site_arr[] = "(SKU = '".$v."' and zhand='".array_get($site_code,str_replace('.','_',$data->site),'')."')";
					
					$rows = $sap->getTureSales(['sku' => $v,'site' => array_get(array_flip(getSapSiteCode()),str_replace('www.','',$data->site)),'month' => date('Ym',strtotime($date_start))]);
					$s_vv001 = array_get($rows,'1.VV001');
					if(substr($s_vv001, -1) =='-'){
						$s_vv001 = round('-'.rtrim($s_vv001, "-"),2);
					}else{
						$s_vv001 = round($s_vv001,2);
					}
					$s_vsrhj = array_get($rows,'1.VSRHJ');
					if(substr($s_vsrhj, -1) =='-'){
						$s_vsrhj = round('-'.rtrim($s_vsrhj, "-"),2);
					}else{
						$s_vsrhj = round($s_vsrhj,2);
					}
					$s_vvvvv = array_get($rows,'1.VVVVV');
					if(substr($s_vvvvv, -1) =='-'){
						$s_vvvvv = round('-'.rtrim($s_vvvvv, "-"),2);
					}else{
						$s_vvvvv = round($s_vvvvv,2); 
					}
					$vv001+=$s_vv001;
					$vsrhj+=$s_vsrhj;
					$vvvvv+=$s_vvvvv;
				}
				
				
			
			}else{
				$sku_site_arr[] = "(SKU = '".$data->item_code."' and zhand='".array_get($site_code,str_replace('.','_',$data->site),'')."')";
				
				$rows = $sap->getTureSales(['sku' => $data->item_code,'site' => array_get(array_flip(getSapSiteCode()),str_replace('www.','',$data->site)),'month' => date('Ym',strtotime($date_start))]);
				$vv001 = array_get($rows,'1.VV001');
				if(substr($vv001, -1) =='-'){
					$vv001 = round('-'.rtrim($vv001, "-"),2);
				}else{
					$vv001 = round($vv001,2);
				}
				$vsrhj = array_get($rows,'1.VSRHJ');
				if(substr($vsrhj, -1) =='-'){
					$vsrhj = round('-'.rtrim($vsrhj, "-"),2);
				}else{
					$vsrhj = round($vsrhj,2);
				}
				$vvvvv = array_get($rows,'1.VVVVV');
				if(substr($vvvvv, -1) =='-'){
					$vvvvv = round('-'.rtrim($vvvvv, "-"),2);
				}else{
					$vvvvv = round($vvvvv,2);
				}
			}
			
			
			$oa_datas = DB::connection('oa')->table('formtable_main_193_dt1')->whereRaw('('.implode(' or ',$sku_site_arr).')')->get();
			
				$oa_datas=json_decode(json_encode($oa_datas), true);
				foreach($oa_datas as $od){
					$oa_data[str_replace('.','',$data->site).'-'.$data->item_code] = $od;
				}
				
			$sap_data[str_replace('.','',$data->site).'-'.$data->item_code] = array(
				'VV001'=>$vv001,
				'VSRHJ'=>$vsrhj,
				'VVVVV'=>$vvvvv,
			);
			
			$last_keywords[str_replace('.','',$data->site).'-'.$data->asin]=Skusweekdetails::where('asin',$data->asin)->where('site',$data->site)->whereNotNull('keywords')->orderBy('weeks','desc')->take(1)->value('keywords');
			
			
			
			
 		}
		for($i=7;$i>=0;$i--){
			$date_arr[]=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
		}
		if($asin_site_arr){
			$datas_item=Skusweekdetails::whereIn('weeks',$date_arr)->whereRaw('('.implode(' or ',$asin_site_arr).')')->get()->toArray();
			
			
			
			
			foreach($datas_item as $di){
				$datas_details[str_replace('.','',$di['site']).'-'.$di['asin'].'-'.$di['weeks']] = $di;
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
		$returnDate['sap_data']= $sap_data;
		$returnDate['last_keywords']= $last_keywords;
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
	    $date_start = $request->get('date_start')?$request->get('date_start'):date('Y-m-d',strtotime('+ 8hours'));
		$week = self::getWeek($date_start);

		
		$where= " length(trim(asin))=10";
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
			$where.= " and site='".$site."'";
		}
		if($user_id){
			$where.= " and sap_seller_id in (".$user_id.")";
		}
		
		if($level){
			$where.= " and sku_tmp_cc.pro_status = '".(($level=='S')?0:$level)."'";
		}
		
		if($sku){
			$where.= " and (asin='".$sku."' or sku_tmp_cc.item_code  like '%".$sku."%')";
		}
		
			
		$month = date('Ym',strtotime($date_start));
        $datas=Skusweekdetails::whereRaw("left(weeks,6)='".$month."' ")->whereRaw($where)
			->leftJoin(DB::raw("(select asin as asin_p,site as site_p,GROUP_CONCAT(a.item_no) as item_code,GROUP_CONCAT(b.item_name) as item_name,max(item_status) as status,
min(status) as pro_status, max(bg) as bg,max(bu) as bu,max(sap_seller_id) as sap_seller_id
from 

(select asin,site,item_no,max(item_status) as item_status,
min(case when status = 'S' Then '0' else status end) as status, 
max(bg) as bg,max(bu) as bu,max(sap_seller_id) as sap_seller_id from asin group by asin,site,item_no)

 as a left join fbm_stock as b on a.item_no=b.item_code   group by asin,site order by pro_status asc ) as sku_tmp_cc"),function($q){
				$q->on('skus_week_details.asin', '=', 'sku_tmp_cc.asin_p')
				  ->on('skus_week_details.site', '=', 'sku_tmp_cc.site_p');
			})
		->orderBy('asin','asc')->orderBy('site','asc')->orderBy('weeks','asc')->get()->toArray();
		
		
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
		$headArray[] = 'Sold Qty Target';
		$headArray[] = 'Sold Qty Completed';
		$headArray[] = 'Sold Qty Completion ratio';
		$headArray[] = 'Sales Target';
		$headArray[] = 'Sales Completed';
		$headArray[] = 'Sales Completion ratio';
		$headArray[] = 'Profit Target';
		$headArray[] = 'Profit Completed';
		$headArray[] = 'Profit Completion ratio';

		$arrayData[] = $headArray;
		$users_array = $this->getUsers();
		$site_code['www_amazon_com']='US';
		$site_code['www_amazon_ca']='CA';
		$site_code['www_amazon_de']='DE';
		$site_code['www_amazon_it']='IT';
		$site_code['www_amazon_es']='ES';
		$site_code['www_amazon_co_uk']='UK';
		$site_code['www_amazon_fr']='FR';
		$site_code['www_amazon_co_jp']='JP';
		$sap = new SapRfcRequest();
		$oa_data=$sap_data=[];
		
		foreach($datas as $data){
			if(!isset($sap_data[str_replace('.','',$data['site']).'-'.$data['item_code']])){
			$sku_site_arr=[];
			$match_sku = explode(',',$data['item_code']);
			if(count($match_sku)>1){
				$vv001=$vsrhj=$vvvvv=0;
				foreach($match_sku as $k=>$v){
					$sku_site_arr[] = "(SKU = '".$v."' and zhand='".array_get($site_code,str_replace('.','_',$data['site']),'')."')";
					
					$rows = $sap->getTureSales(['sku' => $v,'site' => array_get(array_flip(getSapSiteCode()),str_replace('www.','',$data['site'])),'month' => date('Ym',strtotime($date_start))]);
					$s_vv001 = array_get($rows,'1.VV001');
					if(substr($s_vv001, -1) =='-'){
						$s_vv001 = round('-'.rtrim($s_vv001, "-"),2);
					}else{
						$s_vv001 = round($s_vv001,2);
					}
					$s_vsrhj = array_get($rows,'1.VSRHJ');
					if(substr($s_vsrhj, -1) =='-'){
						$s_vsrhj = round('-'.rtrim($s_vsrhj, "-"),2);
					}else{
						$s_vsrhj = round($s_vsrhj,2);
					}
					$s_vvvvv = array_get($rows,'1.VVVVV');
					if(substr($s_vvvvv, -1) =='-'){
						$s_vvvvv = round('-'.rtrim($s_vvvvv, "-"),2);
					}else{
						$s_vvvvv = round($s_vvvvv,2);
					}
					$vv001+=$s_vv001;
					$vsrhj+=$s_vsrhj;
					$vvvvv+=$s_vvvvv;
				}
				
				
			
			}else{
				$sku_site_arr[] = "(SKU = '".$data['item_code']."' and zhand='".array_get($site_code,str_replace('.','_',$data['site']),'')."')";
				
				$rows = $sap->getTureSales(['sku' => $data['item_code'],'site' => array_get(array_flip(getSapSiteCode()),str_replace('www.','',$data['site'])),'month' => date('Ym',strtotime($date_start))]);
				$vv001 = array_get($rows,'1.VV001');
				if(substr($vv001, -1) =='-'){
					$vv001 = round('-'.rtrim($vv001, "-"),2);
				}else{
					$vv001 = round($vv001,2);
				}
				$vsrhj = array_get($rows,'1.VSRHJ');
				if(substr($vsrhj, -1) =='-'){
					$vsrhj = round('-'.rtrim($vsrhj, "-"),2);
				}else{
					$vsrhj = round($vsrhj,2);
				}
				$vvvvv = array_get($rows,'1.VVVVV');
				if(substr($vvvvv, -1) =='-'){
					$vvvvv = round('-'.rtrim($vvvvv, "-"),2);
				}else{
					$vvvvv = round($vvvvv,2);
				}
			}
			
			
			$oa_datas = DB::connection('oa')->table('formtable_main_193_dt1')->whereRaw('('.implode(' or ',$sku_site_arr).')')->get();
			
				$oa_datas=json_decode(json_encode($oa_datas), true);
				foreach($oa_datas as $od){
					$oa_data[str_replace('.','',$data['site']).'-'.$data['item_code']] = $od;
				}
				
			$sap_data[str_replace('.','',$data['site']).'-'.$data['item_code']] = array(
				'VV001'=>$vv001,
				'VSRHJ'=>$vsrhj,
				'VVVVV'=>$vvvvv,
			);
			
			
			
 			}
		
	
			
			
			$target_sold = round(array_get($oa_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.xiaol'.date('n',strtotime($date_start)),0),2);
			if($target_sold>0){
				$complete_sold = round(array_get($sap_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.VV001',0)/$target_sold*100,2);
			}elseif($target_sold<0){
				$complete_sold = round((2-array_get($sap_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.VV001',0)/$target_sold)*100,2);
			}else{
				$complete_sold =0;
			}
			
			$target_sales = round(array_get($oa_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.xiaose'.date('n',strtotime($date_start)),0),2);
			if($target_sales>0){
				$complete_sales = round(array_get($sap_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.VSRHJ',0)/$target_sales*100,2);
			}elseif($target_sales<0){
				$complete_sales = round((2-array_get($sap_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.VSRHJ',0)/$target_sales)*100,2);
			}else{
				$complete_sales =0;
			}
			
			
			$target_pro = round(array_get($oa_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.yewlr'.date('n',strtotime($date_start)),0),2);
			if($target_pro>0){
				$complete_pro = round(array_get($sap_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.VVVVV',0)/$target_pro*100,2);
			}elseif($target_pro<0){
				$complete_pro = round((2-array_get($sap_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.VVVVV',0)/$target_pro)*100,2);
			}else{
				$complete_pro =0;
			}
			
			
			
            $arrayData[] = array(
               	$data['asin'],
				$data['site'],
				$data['item_code'],
				array_get($users_array,intval(array_get($data,'sap_seller_id')),intval(array_get($data,'sap_seller_id'))),
				$data['bg'],
				$data['bu'],
				($data['status'])?'Reserved':'Eliminate',
				($data['pro_status'] == '0')?'S':$data['pro_status'],
				$data['item_name'],
				$data['weeks'],
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
				$data['strategy'],
				
				$target_sold,
				array_get($sap_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.VV001',0),
				$complete_sold.'%',
				
				$target_sales,
				array_get($sap_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.VSRHJ',0),
				$complete_sales.'%',
				
				$target_pro,
				array_get($sap_data,str_replace('.','',$data['site']).'-'.$data['item_code'].'.VVVVV',0),
				$complete_pro.'%'
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