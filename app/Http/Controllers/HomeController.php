<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Groupdetail;
use App\User;
use App\Task;
use App\Asin;
use App\SkusDailyInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
		parent::__construct();
    }	

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$limit_bg = $limit_bu = $limit_sap_seller_id = $limit_review_user_id='';
		
		$sumwhere = '1=1';
		$bonus_point = 0;
		if (Auth::user()->seller_rules) {
			$rules = explode("-", Auth::user()->seller_rules);
			if (array_get($rules, 0) != '*'){
				$limit_bg = array_get($rules, 0);
				$bonus_point = 0.1;
			}else{
				$bonus_point = 1;
			}
			if (array_get($rules, 1) != '*'){
				$limit_bu = array_get($rules, 1);
				$bonus_point = 0.3;
			}
		} elseif (Auth::user()->sap_seller_id) {
			$limit_sap_seller_id = Auth::user()->sap_seller_id;
			$bonus_point = 0.6;
		} else {
			$limit_review_user_id = Auth::user()->id;
			$bonus_point = 0.04;
		}
			
		
		$asins = DB::table( DB::raw("(select max(sku_ranking) as sku_ranking,max(rating) as rating,max(item_no) as item_no,sum(fba_stock+fba_transfer) as fba_stock,sum(sales_07_01) as sales_07_01,sum(sales_14_08) as sales_14_08,sum(sales_21_15) as sales_21_15,sum(sales_28_22) as sales_28_22,max(bg) as bg,max(bu) as bu,max(sap_seller_id) as sap_seller_id,max(review_user_id) as review_user_id, min(case when status = 'S' Then '0' else status end) as status,asin,site from asin where length(asin)=10 group by asin,site) as asin") )->orderByRaw("status asc,sales_07_01 desc")
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
		
		
		$fba_stock_info = DB::select("select sum(fba_stock*fba_cost) as fba_total_amount,sum(fba_stock) as fba_total_stock from (select avg(fba_stock+fba_transfer) as fba_stock,avg(fba_cost) as fba_cost from asin where $sumwhere group by asin,sellersku ) as fba_total");
		
		$fbm_stock_info = DB::select("select sum(fbm_stock*fbm_cost) as fbm_total_amount,sum(fbm_stock) as fbm_total_stock from (select avg(fbm_stock) as fbm_stock,avg(fbm_cost) as fbm_cost from asin where $sumwhere group by item_no) as fbm_total");
		
		
		$asins = $asins->take(5)->get();
		$asins = json_decode(json_encode($asins), true);
		$date_from = $request->get('date_from')?$request->get('date_from'):date('Y-m-d',strtotime('-32days'));
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d',strtotime('-2days'));
		
		$total_info = SkusDailyInfo::select(DB::raw('sum(bonus)*'.$bonus_point.' as bonus,sum(economic) as economic,sum(amount) as amount,sum(sales) as sales'))->whereRaw($sumwhere." and date>='$date_from' and date<='$date_to'")->first()->toArray();
		
		$daily_info = SkusDailyInfo::select(DB::raw('sum(bonus)*'.$bonus_point.' as sumbonus,date'))->whereRaw($sumwhere." and date>='$date_from' and date<='$date_to'")->groupBy(['date'])->pluck('sumbonus','date');
		
		foreach($asins as $key=>$asin){
			$asin_info = SkusDailyInfo::select(DB::raw('sum(bonus)*'.$bonus_point.' as bonus,sum(economic) as economic,sum(amount) as amount,sum(sales) as sales'))->whereRaw("sku='".$asin['item_no']."' and site='".$asin['site']."' and date>='$date_from' and date<='$date_to'")->first()->toArray();
			$asins[$key]['bonus']=$asin_info['bonus'];
			$asins[$key]['economic']=$asin_info['economic'];
			$asins[$key]['amount']=$asin_info['amount'];
			$asins[$key]['sales']=$asin_info['sales'];
			
		}
	
		$returnDate['bonus_point']= $bonus_point;
		$returnDate['total_info']= $total_info;
		$returnDate['daily_info']= $daily_info;
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
	
	public function Export(Request $request){
		$limit_bg = $limit_bu = $limit_sap_seller_id = $limit_review_user_id='';
		
		$sumwhere = '1=1';
		$bonus_point = 0;
		if (Auth::user()->seller_rules) {
			$rules = explode("-", Auth::user()->seller_rules);
			if (array_get($rules, 0) != '*'){
				$limit_bg = array_get($rules, 0);
				$bonus_point = 0.1;
			}else{
				$bonus_point = 1;
			}
			if (array_get($rules, 1) != '*'){
				$limit_bu = array_get($rules, 1);
				$bonus_point = 0.3;
			}
			if(array_get($_REQUEST,'bgbu')){
				$bgbu = array_get($_REQUEST,'bgbu');
				$bgbu_arr = explode('_',$bgbu);
				if(array_get($bgbu_arr,0)) {
					$sumwhere.=" and bg='".array_get($bgbu_arr,0)."'";
				}
				if(array_get($bgbu_arr,1)){
					$sumwhere.=" and bu='".array_get($bgbu_arr,1)."'";
				}
			}
			
			
			if(array_get($_REQUEST,'user_id')){
				$sap_seller_id = User::where('id',array_get($_REQUEST,'user_id'))->value('sap_seller_id');
				$select_user_id=array_get($_REQUEST,'user_id');
				$sumwhere.=" and (sap_seller_id='$sap_seller_id' or review_user_id='$select_user_id')";
			}
		} elseif (Auth::user()->sap_seller_id) {
			$limit_sap_seller_id = Auth::user()->sap_seller_id;
			$bonus_point = 0.6;
		} else {
			$limit_review_user_id = Auth::user()->id;
			$bonus_point = 0.04;
		}
		
		if($limit_bg){

			$sumwhere.=" and bg='$limit_bg'";
		}
		if($limit_bu){

			$sumwhere.=" and bu='$limit_bu'";
		}
		if($limit_sap_seller_id){

			$sumwhere.=" and sap_seller_id='$limit_sap_seller_id'";
		}
		if($limit_review_user_id){

			$sumwhere.=" and review_user_id='$limit_review_user_id'";
		}
		
		
		$date_from = $request->get('date_from')?$request->get('date_from'):date('Y-m-d',strtotime('-32days'));
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d',strtotime('-2days'));

		$total_info = SkusDailyInfo::select(DB::raw('sku,site,sum(amount) as amount,sum(sales) as sales,sum(fulfillmentfee) as fulfillmentfee,sum(commission) as commission,sum(otherfee) as otherfee,sum(refund) as refund,sum(deal) as deal,sum(coupon) as coupon,sum(cpc) as cpc,sum(fbm_storage) as fbm_storage,sum(fba_storage) as fba_storage,sum(amount_used) as amount_used,sum(economic) as economic,sum(bonus) as bonus'))->whereRaw($sumwhere." and date>='$date_from' and date<='$date_to'")->groupBy(['sku','site'])->get()->toArray();
		
		
		
		$spreadsheet = new Spreadsheet();
		$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Total');
		$spreadsheet->addSheet($myWorkSheet, 0);
		$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Details');
		$spreadsheet->addSheet($myWorkSheet, 1);
		$arrayData=[];
		
		$arrayData[] = ['物料号','站点','订单金额','销量','操作费','交易费','其他费用','退款异常','Deal营销费','Coupon营销费','CPC营销费','FBM仓储费','FBA仓储费','资金占用成本','经济效益','提成基数'];
		foreach($total_info as $val){
			$arrayData[] = [
				array_get($val,'sku'),
				array_get($val,'site'),
				array_get($val,'amount'),
				array_get($val,'sales'),
				array_get($val,'fulfillmentfee'),
				array_get($val,'commission'),
				array_get($val,'otherfee'),
				array_get($val,'refund'),
				array_get($val,'deal'),
				array_get($val,'coupon'),
				array_get($val,'cpc'),
				array_get($val,'fbm_storage'),
				array_get($val,'fba_storage'),
				array_get($val,'amount_used'),
				array_get($val,'economic'),
				array_get($val,'bonus')
			];
		}
		$spreadsheet->getSheet(0)->fromArray($arrayData,NULL,'A1');
		
		$arrayData=[];
		$daily_info = SkusDailyInfo::whereRaw($sumwhere." and date>='$date_from' and date<='$date_to'")->orderby('date','asc')->get()->toArray();
		$size_set = ['0'=>'标准','1'=>'大件'];
		$type_set = ['0'=>'淘汰','1'=>'保留','2'=>'新品'];
		$seller_set = User::where('sap_seller_id','>','0')->pluck('name','sap_seller_id');
		$arrayData[] = ['物料号','站点','日期','产品状态','销售员ID','BG','BU','订单金额','销量','操作费','交易费','其他费用','退款异常','Deal营销费','Coupon营销费','CPC营销费','采购成本','体积','尺寸','关税','头程运费','FBM库存','FBM仓储费','FBA库存','FBA仓储费','单位仓储费','人工成本','库存金额','资金占用成本','经济效益','保留品基数','淘汰品基数1','淘汰品基数2','新品销售目标','新品利润目标','新品销售完成率','新品利润完成率','提成基数'];
		foreach($daily_info as $val){
			$arrayData[] = [
				array_get($val,'sku'),
				array_get($val,'site'),
				array_get($val,'date'),
				array_get($type_set,array_get($val,'status'),array_get($val,'status')),
				array_get($seller_set,array_get($val,'sap_seller_id'),array_get($val,'sap_seller_id')),
				array_get($val,'bg'),
				array_get($val,'bu'),
				array_get($val,'amount'),
				array_get($val,'sales'),
				array_get($val,'fulfillmentfee'),
				array_get($val,'commission'),
				array_get($val,'otherfee'),
				array_get($val,'refund'),
				array_get($val,'deal'),
				array_get($val,'coupon'),
				array_get($val,'cpc'),
				array_get($val,'cost'),
				array_get($val,'volume'),
				array_get($size_set,array_get($val,'size'),array_get($val,'size')),
				array_get($val,'tax'),
				array_get($val,'headshipfee'),
				array_get($val,'fbm_stock'),
				array_get($val,'fbm_storage'),
				array_get($val,'fba_stock'),
				array_get($val,'fba_storage'),
				array_get($val,'unit_storage'),
				array_get($val,'cost_set'),
				array_get($val,'stock_amount'),
				
				array_get($val,'amount_used'),
				array_get($val,'economic'),
				
				array_get($val,'reserved'),
				array_get($val,'eliminate1'),
				array_get($val,'eliminate2'),
				array_get($val,'amount_target'),
				array_get($val,'profit_target'),
				array_get($val,'amount_per'),
				array_get($val,'profit_per'),
				array_get($val,'bonus')
			];
		}
		$spreadsheet->getSheet(1)->fromArray($arrayData,NULL,'A1');
		$spreadsheet->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
		header('Content-Disposition: attachment;filename="Export_'.array_get($_REQUEST,'ExportType').'.xlsx"');//告诉浏览器输出浏览器名称
		header('Cache-Control: max-age=0');//禁止缓存
		$writer = new Xlsx($spreadsheet);
		$writer->save('php://output');
	
	}

}