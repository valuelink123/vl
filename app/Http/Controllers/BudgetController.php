<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Budgets;
use App\Budgetdetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BudgetController extends Controller
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

		//if(!Auth::user()->can(['budgets-show'])) die('Permission denied -- budgets-show');
		$site = $request->get('site');
		$bgbu = $request->get('bgbu');
		$sap_seller_id = $request->get('sap_seller_id');
		$level = $request->get('level');
		$sku = $request->get('sku');
	    $year = $request->get('year')?$request->get('year'):date('Y',strtotime('+1 month'));
		$user_id = $request->get('user_id');
		$where = "status in ('New','Normal','Observing','Obsoleted')";
		if (Auth::user()->seller_rules) {
			$rules = explode("-",Auth::user()->seller_rules);
			if(array_get($rules,0)!='*') $where.= " and bg='".array_get($rules,0)."'";
			if(array_get($rules,1)!='*') $where.= " and bu='".array_get($rules,1)."'";
		} elseif (Auth::user()->sap_seller_id) {
			$where.= " and sap_seller_id=".Auth::user()->sap_seller_id;
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
			$where.= " and sap_seller_id in (".implode(',',$user_id).")";
		}
		
		if($level){
			$where.= " and level = '".(($level=='S')?0:$level)."'";
		}
		
		if($sku){
			$where.= " (sku='".$sku."' or description like '%".$sku."%')";
		}
		
		
		$sql = "(select skus.*,case when skus_status.`level` = 'S' Then '0' else skus_status.`level` end as level,skus_status.`status`,skus_status.description from (select item_no as sku,site,max(sap_seller_id) as sap_seller_id,max(bg) as bg,max(bu) as bu from asin
where sap_seller_id<>323 group by sku,site) as skus left join skus_status on skus.sku=skus_status.sku and 
skus.site=skus_status.site) as sku_tmp_cc";
 		$datas = DB::table(DB::raw($sql))->whereRaw($where)->paginate(20);
		
        $data['teams']= DB::select('select bg,bu from asin where sap_seller_id<>323 group by bg,bu ORDER BY BG ASC,BU ASC');
		$data['users']= $this->getUsers();
		$data['year']=$year;
		$data['datas']= $datas;
        return view('budget/index',$data);

    }
	
	
	
	public function edit(Request $request)
    {	
		//if(!Auth::user()->can(['budgets-show'])) die('Permission denied -- budgets-show');
		$sku = $request->get('sku');
		$site = $request->get('site');
		$year = $request->get('year');
		$budget = Budgets::firstOrCreate(['sku'=>$sku,'site'=>$site,'year'=>$year]);
		$budget_id = $budget->id;
		$data['sku']=$sku;
		$data['site']=$site;
		$data['year']=$year;
		$data['budget_id']=$budget_id;
		$data['datas']= [];
		//print_r(getUsers());
		//print_r(getUsers('sap_seller'));
		//print_r(getUsers('sap_bgbu'));
		return view('budget/edit',$data);
    }


	public function export(Request $request){
		if(!Auth::user()->can(['sales-report-export'])) die('Permission denied -- sales-report-export');
		

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