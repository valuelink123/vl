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
		$curr_date = date('Y-m-d');
	    $date_start = $request->get('date_start')?$request->get('date_start'):date('Y-m-d',strtotime('-14 days'));
		$date_end = $request->get('date_end')?$request->get('date_end'):$curr_date;
		if($date_end>$curr_date) $date_end = $curr_date;
		if($date_end<$date_start) $date_start = $date_end;
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
any_value(sap_seller_bg) as bg,any_value(sap_seller_bu) as bu,any_value(sap_seller_id) as sap_seller_id from sap_asin_match_sku where actived=1 group by asin,marketplace_id,sku)

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
		$returnDate['curr_date']= $curr_date;
		$returnDate['s_user_id']= $user_id?$user_id:[];
		$returnDate['bgbu']= $bgbu;
		$returnDate['s_level']= $level;
		$returnDate['sku']= $sku;
		$returnDate['s_site']= $site;
		$returnDate['site_code']= $site_code;
		$returnDate['datas']= $datas;
        return view('sku/index',$returnDate);

    }

	public function upload( Request $request )
    {
		$updateData=[];
		try{
			$file = $request->file('file');
			$originalName = $file->getClientOriginalName();
			$ext = $file->getClientOriginalExtension();
			$type = $file->getClientMimeType();
			$realPath = $file->getRealPath();
			$newname = date('His').uniqid().'.'.$ext;
			$newpath = '/uploads/dailyReport/'.date('Ymd').'/';
			$inputFileName = public_path().$newpath.$newname;
			$bool = $file->move(public_path().$newpath,$newname);
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
			$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
			$i=0;
			foreach($importData as $key => $data){
				if($key==1) continue;
				$keyword = trim(array_get($data,'H'));
				$rank = trim(array_get($data,'I'));
				$asin = trim(array_get($data,'A'));
				$site = trim(array_get($data,'B'));
				$date = trim(array_get($data,'C'));
				if($asin && $site && $date){
					$i++;
					$row=['asin'=>$asin,'site'=>$site,'date'=>$date];
					if(trim(array_get($data,'D'))) $row['ranking'] = trim(array_get($data,'D'));
					if(trim(array_get($data,'E'))) $row['flow'] = trim(array_get($data,'E'));
					if(trim(array_get($data,'F'))) $row['conversion'] = trim(array_get($data,'F'));
					if(trim(array_get($data,'G'))) $row['strategy'] = trim(array_get($data,'G'));
					if(trim(array_get($data,'H'))){
						$row['keywords'][trim(array_get($data,'H'))] = trim(array_get($data,'I'));
					}
					$updateData[$i] = $row ;
				}else{
					$updateData[$i]['keywords'][trim(array_get($data,'H'))] = trim(array_get($data,'I'));
				}
			}
			$records["updateData"] = $updateData;
			$records["customActionStatus"] = 'OK';
			$records["customActionMessage"] = 'Upload Successed!';  
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
		}    
        echo json_encode($records);  
	}
	
	public function export(Request $request){
		if(!Auth::user()->can(['sales-report-export'])) die('Permission denied -- sales-report-export');
		set_time_limit(0);
		$site = $request->get('site');
		$bgbu = $request->get('bgbu');
		$user_id = $request->get('user_id');
		$sku = trim($request->get('sku'));
		$level = $request->get('level');
	    $date_start = $request->get('date_start')?date('Y-m-d',strtotime($request->get('date_start'))):date('Ymd',strtotime('-14 days'));
		$date_end = $request->get('date_end')?date('Y-m-d',strtotime($request->get('date_end'))):date('Ymd');
		if($date_end<$date_start) $date_end = $date_start;
		$users_array = getUsers('sap_seller');
		$exportFileName = $date_start.'_'.$date_end;
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
		   $exportFileName .= '_'.$bgbu;
		}
		if($site){
			$where.= " and marketplace_id='".$site."'";
			$exportFileName .= '_'.array_get(getSiteUrl(),$site,$site);
		}
		if($user_id){
			$where.= " and sap_seller_id in (".$user_id.")";
			$exportFileName .= '_'.implode(',',$user_id);
		}
		if($level){
			$where.= " and sku_tmp_cc.pro_status = '".(($level=='S')?0:$level)."'";
			$exportFileName .= '_'.(($level=='S')?0:$level);
		}
		
		if($sku){
			$where.= " and (asin='".$sku."' or sku_tmp_cc.sku  like '%".$sku."%' or sku_tmp_cc.description like '%".$sku."%')";
			$exportFileName .= '_'.$sku;
		}
		$exportFileName.='_'.date('YmdHis').'.xls';
			
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
		$headArray[] = 'Date';
		$headArray[] = 'Ranking';
		$headArray[] = 'Session';
		$headArray[] = 'Conversion%';
		$headArray[] = 'Strategy';
		$headArray[] = 'Main Keywords';
		$headArray[] = 'Keywords Ranking';
		$headArray[] = 'Rating';
		$headArray[] = 'Review';
		$headArray[] = 'Sales';
		$headArray[] = 'Price';
		$headArray[] = 'FBA Stock';
		$headArray[] = 'FBA Transfer';
		$headArray[] = 'FBM Stock';
		$headArray[] = 'Total Stock';
		$headArray[] = 'FBA Keep';
		$headArray[] = 'Total Keep';
		$headArray[] = 'Sku';
		$headArray[] = 'Seller';
		$headArray[] = 'BG';
		$headArray[] = 'BU';
		$headArray[] = 'Status';
		$headArray[] = 'Level';
		$headArray[] = 'Description';
		$arrayData[] = $headArray;
		
		foreach($datas as $data){
			$keywords = json_decode($data['keywords'],true);
			if(empty($keywords) || !is_array($keywords)){
				$arrayData[] = array(
					$data['asin'],
					array_get(getSiteUrl(),$data['marketplace_id']),
					$data['date'],
					$data['ranking'],
					$data['flow'],
					$data['conversion']*100,
					$data['strategy'],
					'',
					'',
					$data['rating'],
					$data['review'],
					$data['sales'],
					$data['price'],	
					$data['fba_stock'],
					$data['fba_transfer'],
					$data['fbm_stock'],
					intval($data['fba_stock']+$data['fba_transfer']+$data['fbm_stock']),
					($data['sales'])?round(intval($data['fba_stock'])/$data['sales'],2):'∞',
					($data['sales'])?round((intval($data['fba_stock'])+intval($data['fba_transfer'])+intval($data['fbm_stock']))/$data['sales'],2):'∞',
					$data['sku'],
					array_get($users_array,intval(array_get($data,'sap_seller_id')),intval(array_get($data,'sap_seller_id'))),
					$data['bg'],
					$data['bu'],
					array_get(getSkuStatuses(),$data['status']),
					($data['pro_status']==='0')?'S':$data['pro_status'],
					$data['description'],
				);
			}else{
				$i=0;
				foreach($keywords as $keyword=>$rank){
					$arrayData[] = array(
						((!$i)?$data['asin']:''),
						(!$i)?array_get(getSiteUrl(),$data['marketplace_id']):'',
						(!$i)?$data['date']:'',
						(!$i)?$data['ranking']:'',
						(!$i)?$data['flow']:'',
						(!$i)?($data['conversion']*100):'',
						(!$i)?$data['strategy']:'',
						$keyword,
						$rank,
						(!$i)?$data['rating']:'',
						(!$i)?$data['review']:'',
						(!$i)?$data['sales']:'',
						(!$i)?$data['price']:'',	
						(!$i)?$data['fba_stock']:'',
						(!$i)?$data['fba_transfer']:'',
						(!$i)?$data['fbm_stock']:'',
						(!$i)?intval($data['fba_stock']+$data['fba_transfer']+$data['fbm_stock']):'',
						(!$i)?(($data['sales'])?round(intval($data['fba_stock'])/$data['sales'],2):'∞'):'',
						(!$i)?(($data['sales'])?round((intval($data['fba_stock'])+intval($data['fba_transfer'])+intval($data['fbm_stock']))/$data['sales'],2):'∞'):'',
						(!$i)?$data['sku']:'',
						(!$i)?array_get($users_array,intval(array_get($data,'sap_seller_id')),intval(array_get($data,'sap_seller_id'))):'',
						(!$i)?$data['bg']:'',
						(!$i)?$data['bu']:'',
						(!$i)?array_get(getSkuStatuses(),$data['status']):'',
						(!$i)?(($data['pro_status']==='0')?'S':$data['pro_status']):'',
						(!$i)?$data['description']:'',
					);
					$i++;
				}
			}   
		}

		if($arrayData){
			$spreadsheet = new Spreadsheet();
			$spreadsheet->getActiveSheet()->fromArray($arrayData,NULL, 'A1' );
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$exportFileName.'.xlsx"');
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}
	
    public function keywords(Request $request){
		$asin = $request->input('asin');
		$marketplace_id = $request->input('marketplace_id');
		$date = $request->input('date');
		$data = Skusweekdetails::where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('date',$date)->first();
		if(empty($data) || empty(json_decode($data->keywords,true))){
			$lastKeywords = Skusweekdetails::where('asin',$asin)->where('marketplace_id',$marketplace_id)
			->whereNotNull('keywords')->orderBy('date','desc')->take(1)->value('keywords');
			$lastKeywords = json_decode($lastKeywords,true);
			if(empty($lastKeywords)){
				$keyword=[];
			}else{
				foreach($lastKeywords as $key=>$val){
					$keyword[$key]=0;
				}
			}
			
		}else{
			$keyword = json_decode($data->keywords,true);
		}
        return view('sku/keywords',[
			'keywords'=>$keyword,
			'asin'=>$asin,
			'marketplace_id'=>$marketplace_id,
			'date'=>$date,
			]);
	}

	public function updatekeywords(Request $request){
		DB::beginTransaction();
        try{ 
			$asin = $request->input('asin');
			$marketplace_id = $request->input('marketplace_id');
			$date = $request->input('date');
			if(!$asin || !$marketplace_id || !$date) throw new \Exception('Invalid Params',10001);
			$keywords = $request->input('keywords')??[];
			$dataKeywords = [];
			foreach($keywords as $keyword){
				$dataKeywords[$keyword['keyword']]=$keyword['rank'];
			}
			$data = Skusweekdetails::updateOrCreate(
				[
					'asin'=>$asin,
					'marketplace_id'=>$marketplace_id,
					'date'=>$date,
				],
				[
					'keywords'=>$dataKeywords?json_encode($dataKeywords):NULL,
				]
			);
			if(!empty(json_decode($data->keywords,true))){
				$rankHtml='';
				foreach(json_decode($data->keywords,true) as $key=>$val){
					$rankHtml .= $val.' in '.$key.'</BR>';
				}
			}else{
				$rankHtml = 'N/A';
			}
			
			DB::commit();
			$lastKeywords = Skusweekdetails::where('asin',$asin)->where('marketplace_id',$marketplace_id)
			->whereNotNull('keywords')->orderBy('date','desc')->first();
			if(!empty(json_decode($lastKeywords->keywords,true))){
				$keywordHtml='';
				foreach(json_decode($lastKeywords->keywords,true) as $key=>$val){
					$keywordHtml .=$key.', ';
				}
			}else{
				$keywordHtml = 'N/A';
			}
			

			
            $records["customActionStatus"] = 'OK';
			$records["customActionMessage"] = 'Update Successed!';  
			$records["ajaxReplace"] = [
				$asin.$marketplace_id.$date.'ranks'=>$rankHtml,
				$asin.$marketplace_id.'keywords'=>$keywordHtml,
			];   
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);  	
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
		
		$return[$name]=$request->get('value');
		
		echo json_encode($return);
				
	}
	
	public function batchUpdate(Request $request)
    {
        set_time_limit(0);
        DB::beginTransaction();
        try{ 
			$datas = $request->all();
			foreach($datas as $data){
				$asin = array_get($data,'asin');
				$marketplace_id = array_get(array_flip(getSiteUrl()),array_get($data,'site'));
				$date = date('Y-m-d',strtotime(array_get($data,'date')));
				if(!$asin || !$marketplace_id || !$date) continue;
				unset($data['asin']);unset($data['site']);unset($data['date']);
				if(isset($data['keywords'])) $data['keywords'] = json_encode($data['keywords']);
				if(isset($data['keywords'])) $data['conversion'] = round($data['conversion']/100,4);
				Skusweekdetails::updateOrCreate(
					[
						'asin'=>$asin,
						'marketplace_id'=>$marketplace_id,
						'date'=>$date,
					],
					$data
				);

			}
            DB::commit();
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = '更新成功!';     
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);  
    }



}