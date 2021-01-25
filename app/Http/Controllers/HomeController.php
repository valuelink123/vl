<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleDailyInfo;
use App\SapAsinMatchSku;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
		$date_from = $request->get('date_from')?$request->get('date_from'):(date('Y-m',strtotime('-2days')).'-01');
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d',strtotime('-2days'));
		if($date_to>date('Y-m-d',strtotime('-2days'))) $date_to=date('Y-m-d',strtotime('-2days'));
		if($date_from>$date_to) $date_from=$date_to;
		
		$datas  = new SaleDailyInfo;
		$user_teams = new SapAsinMatchSku;
		if (Auth::user()->seller_rules) {
			$rules = explode("-", Auth::user()->seller_rules);
			if (array_get($rules, 0) != '*'){
				$datas = $datas->where('sap_seller_bg',array_get($rules, 0));
				$user_teams =$user_teams->where('sap_seller_bg',array_get($rules, 0));
			}
			if (array_get($rules, 1) != '*'){
				$datas =$datas->where('sap_seller_bu',array_get($rules, 1));
				$user_teams =$user_teams->where('sap_seller_bu',array_get($rules, 1));
			}
		} else {
			$datas =$datas->where('sap_seller_id',Auth::user()->sap_seller_id); 
			$user_teams =$user_teams->where('sap_seller_id',Auth::user()->sap_seller_id);
		} 

		$user_teams = $user_teams->selectRaw('sap_seller_bg as bg , sap_seller_bu as bu')
		->groupBy(['bg','bu'])->orderBy('bg','asc')->orderBy('bu','asc')->get();
		
		if(array_get($_REQUEST,'sap_seller_id')){
			$datas =$datas->where('sap_seller_id',array_get($_REQUEST,'sap_seller_id')); 
		}

		if(array_get($_REQUEST,'bgbu')){
			$bgbu = array_get($_REQUEST,'bgbu');
			$bgbu_arr = explode('_',$bgbu);
			if(array_get($bgbu_arr,0)) {
				$datas =$datas->where('sap_seller_bg',array_get($bgbu_arr, 0));
			}
			if(array_get($bgbu_arr,1)){
				$datas =$datas->where('sap_seller_bu',array_get($bgbu_arr, 1));
			}
		}
		if(array_get($_REQUEST,'keywords')){
			$keywords = array_get($_REQUEST,'keywords');
			if($keywords){
				$search_keys = explode(',', $keywords);
				$datas = $datas->where(function ($query) use ($search_keys) {
					foreach($search_keys as $search_key){
						$query->where('sku', 'like', '%'.$search_key.'%')
						->orWhere('asin', 'like', '%'.$search_key.'%');
					}
				});	
			}
		}
		
		if(array_get($_REQUEST,'sku_status')!==NULL && array_get($_REQUEST,'sku_status')!==''){
			$datas =$datas->where('sku_status',array_get($_REQUEST,'sku_status'));
		}
		
		$total_info = clone $datas;
		$hb_total_info = clone $datas;

		$datas =$datas->where('date','>=',$date_from)->where('date','<=',$date_to)->selectRaw('
			asin,
			marketplace_id,
			any_value(sku) as sku,
			any_value(sku_status) as sku_status,
			sum(amount) as amount,
			sum(resvered) as resvered,
			sum(sale) as sale,
			sum(income) as income,
			sum(shipped) as shipped,
			sum(`replace`) as `replace`,
			sum(profit) as profit,
			sum(refund) as refund,
			sum(`return`) as `return`,
			sum(cost) as cost,
			sum(economic) as economic
			')
		->groupBy(['asin','marketplace_id'])->orderBy("economic","desc")->get()->toArray();
		
		

		$sku_statuses = getSkuStatuses();
		
		$hb_date_to = date('Y-m-d',strtotime($date_from)-86400);
		$hb_date_from = date('Y-m-d',2*strtotime($date_from)-strtotime($date_to)-86400);
		
		$total_info = $total_info->where('date','>=',$date_from)->where('date','<=',$date_to)
		->selectRaw('
			sum(amount) as amount,
			sum(resvered) as resvered,
			sum(sale) as sale,
			sum(income) as income,
			sum(shipped) as shipped,
			sum(`replace`) as `replace`,
			sum(profit) as profit,
			sum(refund) as refund,
			sum(`return`) as `return`,
			sum(cost) as cost,
			sum(economic) as economic
			')
		->first()->toArray();
		
		$hb_total_info = $hb_total_info->where('date','>=',$hb_date_from)->where('date','<=',$hb_date_to)
		->selectRaw('
			sum(amount) as amount,
			sum(resvered) as resvered,
			sum(sale) as sale,
			sum(income) as income,
			sum(shipped) as shipped,
			sum(`replace`) as `replace`,
			sum(profit) as profit,
			sum(refund) as refund,
			sum(`return`) as `return`,
			sum(cost) as cost,
			sum(economic) as economic
			')
		->first()->toArray();
	
		$returnDate['total_info']= $total_info;
		$returnDate['hb_total_info']= $hb_total_info;
		$returnDate['datas']= $datas;
		$returnDate['teams']= $user_teams;
		$returnDate['sku_statuses']= $sku_statuses;
		$returnDate['selected_sku_status']= $request->get('sku_status');
		$returnDate['selected_keywords']= $request->get('keywords');
		$returnDate['users']= getUsers('sap_seller');
		$returnDate['date_from']= $date_from;
		$returnDate['date_to']= $date_to;
		$returnDate['selected_sap_seller_id']= $request->get('sap_seller_id');
		$returnDate['bgbu']= $request->get('bgbu');
        return view('home',$returnDate);

    }
	
	
	public function Export(Request $request){
		$limit_bg = $limit_bu = $limit_sap_seller_id='';
		if (Auth::user()->seller_rules) {
			$rules = explode("-", Auth::user()->seller_rules);
			if (array_get($rules, 0) != '*'){
				$limit_bg = array_get($rules, 0);
			}
			if (array_get($rules, 1) != '*'){
				$limit_bu = array_get($rules, 1);
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
				$user_info = User::where('id',array_get($_REQUEST,'user_id'))->first();
				
				if ($user_info->seller_rules) {
					$rules = explode("-", $user_info->seller_rules);
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
				} elseif ($user_info->sap_seller_id) {
					$limit_sap_seller_id = $user_info->sap_seller_id;
					$bonus_point = 0.6;
				} else {
					$limit_review_user_id = $user_info->id;
					$bonus_point = 0.04;
				}
			}
		} elseif (Auth::user()->sap_seller_id) {
			$limit_sap_seller_id = Auth::user()->sap_seller_id;
		} else {
			$limit_sap_seller_id = Auth::user()->id;
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
		
		
		$date_from = $request->get('date_from')?$request->get('date_from'):(date('Y-m',strtotime('-2days')).'-01');
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d',strtotime('-2days'));
		
		if($date_to>date('Y-m-d',strtotime('-2days'))) $date_to=date('Y-m-d',strtotime('-2days'));
		if($date_from>$date_to) $date_from=$date_to;
		
		$date_month = date('Y-m',strtotime($date_from));

		$total_info = SkusDailyInfo::select(DB::raw('sku,site,sum(amount) as amount,sum(sales) as sales,sum((cost+tax+headshipfee)*sales) as total_cost,sum(fulfillmentfee) as fulfillmentfee,sum(commission) as commission,sum(otherfee) as otherfee,sum(returnqty) as returnqty,sum(deal) as deal,sum(coupon) as coupon,sum(cpc) as cpc,sum(fbm_storage) as fbm_storage,sum(fba_storage) as fba_storage,sum(amount_used) as amount_used,sum(economic) as economic,sum(profit) as profit,sum(amount_target) as amount_target,sum(sales_target) as sales_target,sum(profit_target) as profit_target,sum(bonus) as bonus'))->whereRaw($sumwhere." and date>='$date_from' and date<='$date_to'")->groupBy(['sku','site'])->get()->toArray();
		
		
		
		$spreadsheet = new Spreadsheet();
		$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Total');
		$spreadsheet->addSheet($myWorkSheet, 0);
		$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Details');
		$spreadsheet->addSheet($myWorkSheet, 1);
		$arrayData=[];
		
		$arrayData[] = ['物料号','站点','订单金额','销量','总成本','操作费','交易费','其他费用','退货数量','Deal营销费','Coupon营销费','CPC营销费','FBM仓储费','FBA仓储费','资金占用成本','经济效益','业务净利润','销量目标','销量完成率','销售额目标','销售额完成率','利润目标','利润完成率','提成基数'];
		foreach($total_info as $val){
		
			if(array_get($val,'sales_target')<0){
				$sales_per = round(2-array_get($val,'sales')/array_get($val,'sales_target'),4);
			}elseif(array_get($val,'sales_target')>0){
				$sales_per = round(array_get($val,'sales')/array_get($val,'sales_target'),4);
			}else{
				$sales_per =0;
			}
			
			if(array_get($val,'amount_target')<0){
				$amount_per = round(2-array_get($val,'amount')/array_get($val,'amount_target'),4);
			}elseif(array_get($val,'amount_target')>0){
				$amount_per =round(array_get($val,'amount')/array_get($val,'amount_target'),4);
			}else{
				$amount_per =0;
			}
			
			if(array_get($val,'profit_target')<0){
				$profit_per = round(2-array_get($val,'profit')/array_get($val,'profit_target'),4);
			}elseif(array_get($val,'profit_target')>0){
				$profit_per =round(array_get($val,'profit')/array_get($val,'profit_target'),4);
			}else{
				$profit_per =0;
			}
			$arrayData[] = [
				array_get($val,'sku'),
				array_get($val,'site'),
				array_get($val,'amount'),
				array_get($val,'sales'),
				round(array_get($val,'total_cost'),2),
				array_get($val,'fulfillmentfee'),
				array_get($val,'commission'),
				array_get($val,'otherfee'),
				array_get($val,'returnqty'),
				array_get($val,'deal'),
				array_get($val,'coupon'),
				array_get($val,'cpc'),
				array_get($val,'fbm_storage'),
				array_get($val,'fba_storage'),
				array_get($val,'amount_used'),
				array_get($val,'economic'),
				array_get($val,'profit'),
				array_get($val,'sales_target'),
				$sales_per,
				array_get($val,'amount_target'),
				$amount_per,
				array_get($val,'profit_target'),
				$profit_per,
				array_get($val,'bonus')
			];
		}
		$spreadsheet->getSheet(0)->fromArray($arrayData,NULL,'A1');
		
		$arrayData=[];
		$daily_info = SkusDailyInfo::whereRaw($sumwhere." and date>='$date_from' and date<='$date_to'")->orderby('date','asc')->get()->toArray();
		$size_set = ['0'=>'标准','1'=>'大件'];
		$type_set = ['0'=>'淘汰','1'=>'保留','2'=>'新品'];
		$seller_set = User::where('sap_seller_id','>','0')->pluck('name','sap_seller_id');
		$arrayData[] = ['物料号','站点','日期','产品状态','销售员ID','BG','BU','采购单价','体积','尺寸','关税单价','头程运费单价','单位仓储费','人工成本','订单金额','销量','操作费','交易费','其他费用','退货数量','Deal营销费','Coupon营销费','CPC营销费','FBM库存','FBM仓储费','FBA库存','FBA仓储费','库存金额','资金占用成本','经济效益','业务净利润','保留品基数','淘汰品基数1','淘汰品基数2','销量目标','销量完成率','销售额目标','销售额完成率','利润目标','利润完成率','提成基数'];
		foreach($daily_info as $val){
			$arrayData[] = [
				array_get($val,'sku'),
				array_get($val,'site'),
				array_get($val,'date'),
				array_get($type_set,array_get($val,'status'),array_get($val,'status')),
				array_get($seller_set,array_get($val,'sap_seller_id'),array_get($val,'sap_seller_id')),
				array_get($val,'bg'),
				array_get($val,'bu'),
				array_get($val,'cost'),
				array_get($val,'volume'),
				array_get($size_set,array_get($val,'size'),array_get($val,'size')),
				array_get($val,'tax'),
				array_get($val,'headshipfee'),
				array_get($val,'unit_storage'),
				array_get($val,'cost_set'),
				array_get($val,'amount'),
				array_get($val,'sales'),
				array_get($val,'fulfillmentfee'),
				array_get($val,'commission'),
				array_get($val,'otherfee'),
				array_get($val,'returnqty'),
				array_get($val,'deal'),
				array_get($val,'coupon'),
				array_get($val,'cpc'),
				array_get($val,'fbm_stock'),
				array_get($val,'fbm_storage'),
				array_get($val,'fba_stock'),
				array_get($val,'fba_storage'),
				
				array_get($val,'stock_amount'),
				
				array_get($val,'amount_used'),
				array_get($val,'economic'),
				array_get($val,'profit'),
				array_get($val,'reserved'),
				array_get($val,'eliminate1'),
				array_get($val,'eliminate2'),
				array_get($val,'sales_target'),
				array_get($val,'sales_per'),
				array_get($val,'amount_target'),
				array_get($val,'amount_per'),
				array_get($val,'profit_target'),
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