<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Budgets;
use App\Budgetskus;
use App\Budgetdetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\TaxRate;

class ReqrevController extends Controller
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
		if(!Auth::user()->can(['reqrev-show'])) die('Permission denied -- reqrev-show');
		
		if($request->isMethod('POST') && $request->get('asin_site') && $request->get('BatchUpdate')){
			try{
				$delete_where = $add_where = [];
				foreach($request->get('asin_site') as $k => $v){
					$asin_site = explode('_',$v);
					$asin = $asin_site[0];
					$site = $asin_site[1];
					$delete_where[]= "(asin='$asin' and site='$site')";
					$add_where[]= ['asin'=>$asin, 'site'=>$site];
				}
				if($request->get('BatchUpdate')=='Enable'){
					DB::table('auto_request_asin')->insert($add_where);
				}
				if($request->get('BatchUpdate')=='Disable'){
					DB::table('auto_request_asin')->whereRaw(implode(' or ',$delete_where))->delete();
				}
			
				$request->session()->flash('success_message','Update Success!');
			} catch (\Exception $e) {
				$request->session()->flash('error_message','Update Failed!');
			} 
		}
		
		$site = $request->get('site');
		$bgbu = $request->get('bgbu');
		$status = $request->get('status');
		$sap_seller_id = $request->get('sap_seller_id');
		$sku = $request->get('sku');
		$user_id = $request->get('user_id');
		$where = "1=1";
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
		if($sku){
			$where.= " and (asin='".$sku."' or sku like '%".$sku."%')";
		}
		
		if($status){
			if($status=='enabled'){
				$where.= " and id >0";
			}else{
				$where.= " and id is null";
			}
			
		}
		
		$sql = "(select a.*,b.id,c.success,c.failed from 
(select asin,site,any_value(item_no) as sku,any_value(sap_seller_id) as sap_seller_id,any_value(bg) as bg,any_value(bu) as bu from asin 
where bg<>'BG' and bu<>'BG' and sap_seller_id<>323 and length(asin)=10
group by asin,site)
as a left join 
auto_request_asin as b on a.asin=b.asin and a.site=b.site
left join (select sum(case when status=1 then count else 0 end) as success, sum(case when status=-1 then count else 0 end) as failed,asin,site
from (select count(*) as count,status,asin,site from auto_request_review group by asin,site,status) as c_1 group by asin,site) as c
on a.asin=c.asin and a.site=c.site) as sku_tmp_cc";

		$sum = DB::table(DB::raw($sql))->whereRaw($where)->selectRaw('sum(success) as success,sum(failed) as failed')->first();
		
				
		
	
		
		
 		$datas = DB::table(DB::raw($sql))->whereRaw($where)->paginate(20);
		
        $data['teams']= getUsers('sap_bgbu');
		$data['users']= getUsers('sap_seller');
		$data['datas']= $datas;
		$data['sum']= $sum;
        return view('reqrev/index',$data);
    }
	
}