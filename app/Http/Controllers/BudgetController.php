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

	public $week_per = ['0'=>1.13/7.01,'1'=>1.12/7.01,'2'=>1.09/7.01,'3'=>1.04/7.01,'4'=>0.91/7.01,'5'=>0.86/7.01,'6'=>0.86/7.01];
	
    public function index(Request $request)
    {	
		if(!Auth::user()->can(['budgets-show'])) die('Permission denied -- budgets-show');
		
		if($request->isMethod('POST') && $request->get('budget_id')){ 
			Budgets::whereIn('id',$request->get('budget_id'))->update(['status'=>$request->get('BatchUpdate')]);
			$request->session()->flash('success_message','Update Success!'); 
		}
		
		$site = $request->get('site');
		$bgbu = $request->get('bgbu');
		$sap_seller_id = $request->get('sap_seller_id');
		$level = $request->get('level');
		$sku = $request->get('sku');

		$nowYear = date('Y');
	    $nowMonth =  date('m');
	    if($nowMonth>=1) $now_quarter = 2;
	    if($nowMonth>=4) $now_quarter = 3;
	    if($nowMonth>=7) $now_quarter = 4;
	    if($nowMonth>=10){
	    	$now_quarter = 1;
	    	$nowYear=$nowYear+1;
	    }
	    $default_year_from = $nowYear.'Ver'.$now_quarter;
	    $default_year_to = ($now_quarter==1)?($nowYear-1).'Ver4':$nowYear.'Ver'.($now_quarter-1);

	    $year_from = $request->get('year_from')?$request->get('year_from'):$default_year_from;
	    $year_to = $request->get('year_to')?$request->get('year_to'):$default_year_to;
	    $year_from_arr = explode('Ver', $year_from);
	    $year_to_arr = explode('Ver', $year_to);


	    $quarter_from = $request->get('quarter_from');
	    
	    if($quarter_from){
	    	$date_add_from = '( 1=2';
	    	foreach($quarter_from as $k=>$v){
	    		$date_add_from.=" or (date between '".$year_from_arr[0]."-".sprintf("%02d",(($v-1)*3+1))."-01' and '".$year_from_arr[0]."-".sprintf("%02d",($v*3))."-31')";	
	    	}
	    	$date_add_from.=')';
		}else{
			$date_add_from = " date between '".$year_from_arr[0]."-01-01' and '".$year_from_arr[0]."-12-31' ";
		}
		
		$quarter_to = $request->get('quarter_to');
		if($quarter_to){
	    	$date_add_to = ' (1=2';
	    	foreach($quarter_from as $k=>$v){
	    		$date_add_to.=" or (date between '".$year_to_arr[0]."-".sprintf("%02d",(($v-1)*3+1))."-01' and '".$year_to_arr[0]."-".sprintf("%02d",($v*3))."-31') ";	
	    	}
	    	$date_add_to.=')';
		}else{
			$date_add_to = " date between '".$year_to_arr[0]."-01-01' and '".$year_to_arr[0]."-12-31' ";
		}

		$user_id = $request->get('user_id');
		$sku_status = $request->get('sku_status');
		$b_status = $request->get('b_status');
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
		
		if($level){
			$where.= " and level = '".$level."'";
		}
		if($sku_status){
			$where.= " and status = '".($sku_status-1)."'";
		}
		if($sku){
			$search_skus = explode(',', $sku);
			$where.= " and ( sku='".$sku."' ";
			foreach($search_skus as $search_sku){
				$where.= " or sku='".$search_sku."'";
			}
			$where.= ")";
		}
		
		$table_from = "select a.sku,a.site,a.id,a.status,a.remark,b.* from budgets as a left join (select budget_id,sum(qty+promote_qty) as qty,sum(income) as income,sum(cost) as cost,sum(common_fee) as common_fee,sum(pick_fee) as pick_fee,sum(promotion_fee) as promotion_fee,sum(amount_fee) as amount_fee,sum(storage_fee) as storage_fee from budget_details
where ".$date_add_from." group by budget_id) as b on a.id=b.budget_id where a.year=".$year_from_arr[0]." and a.quarter=".$year_from_arr[1]." and a.status>0";
		
		$table_to = "select a.sku,a.site,b.* from budgets as a left join (select budget_id,sum(qty+promote_qty) as qty,sum(income) as income,sum(cost) as cost,sum(common_fee) as common_fee,sum(pick_fee) as pick_fee,sum(promotion_fee) as promotion_fee,sum(amount_fee) as amount_fee,sum(storage_fee) as storage_fee from budget_details
where ".$date_add_to." group by budget_id) as b on a.id=b.budget_id where a.year=".$year_to_arr[0]." and a.quarter=".$year_to_arr[1]." and a.status>0";

		$table_current = "select sku,site,sum(sales) as qty,sum(amount) as income,sum((cost+tax+headshipfee)*sales) as cost,-1*sum(commission) as common_fee,-1*sum(fulfillmentfee) as pick_fee,sum(deal+cpc+coupon) as promotion_fee,
sum(amount_used) as amount_fee, sum(fba_storage+fbm_storage) as storage_fee from skus_daily_info where ".$date_add_from." group by  sku,site";
		
		
		$sql = "(
		select budget_skus.*,budgets_1.qty as qty1,budgets_1.income as amount1,(budgets_1.income-budgets_1.cost) as profit1,(budgets_1.income-budgets_1.cost-budgets_1.common_fee-budgets_1.pick_fee-budgets_1.promotion_fee-budgets_1.amount_fee-budgets_1.storage_fee) as economic1,IFNULL(budgets_1.id,0) as budget_id,IFNULL(budgets_1.status,0) as budget_status,budgets_1.remark
,budgets_2.qty as qty2,budgets_2.income as amount2,(budgets_2.income-budgets_2.cost) as profit2,(budgets_2.income-budgets_2.cost-budgets_2.common_fee-budgets_2.pick_fee-budgets_2.promotion_fee-budgets_2.amount_fee-budgets_2.storage_fee) as economic2, budgets_3.qty as qty3,budgets_3.income as amount3,(budgets_3.income-budgets_3.cost) as profit3,(budgets_3.income-budgets_3.cost-budgets_3.common_fee-budgets_3.pick_fee-budgets_3.promotion_fee-budgets_3.amount_fee-budgets_3.storage_fee) as economic3 from budget_skus 
		left join (".$table_from." ) as budgets_1 
		on budget_skus.sku = budgets_1.sku and budget_skus.site = budgets_1.site
		left join (".$table_to.") as budgets_2 
		on budget_skus.sku = budgets_2.sku and budget_skus.site = budgets_2.site
		left join (".$table_current.") as budgets_3 
		on budget_skus.sku = budgets_3.sku and budget_skus.site = budgets_3.site
		) as sku_tmp_cc";
		
		$finish = DB::table(DB::raw($sql))->whereRaw($where." and (stock>=100 or (status<>0 and status <>4 and status <>5 and status <>3)) ")->selectRaw('count(*) as count,budget_status')->groupBy('budget_status')->pluck('count','budget_status');

		
		if($b_status){
			$where.= " and budget_status = '".($b_status-1)."'";
		}
		
		
		$sum = DB::table(DB::raw($sql))->whereRaw($where)->selectRaw('sum(stock) as stock,sum(qty1) as qty1,sum(amount1) as amount1,sum(economic1) as economic1,sum(qty2) as qty2,sum(amount2) as amount2,sum(economic2) as economic2,sum(qty3) as qty3,sum(amount3) as amount3,sum(economic3) as economic3')->first();
		
				
		
	
		
		
 		$datas = DB::table(DB::raw($sql))->whereRaw($where)->orderByRaw("case when level = 'S' Then '0' else level end asc")->paginate(20);
		
        $data['teams']= getUsers('sap_bgbu');
		$data['users']= getUsers('sap_seller');
		$data['sku_status']= getSkuStatuses();
		$data['year']=$year_from_arr[0];
		$data['quarter']=$year_from_arr[1];
		$data['year_from']=$year_from;
		$data['year_to']=$year_to;

		$data['quarter_from']=$quarter_from;
		$data['quarter_to']=$quarter_to;

		$data['datas']= $datas;
		$data['finish']= $finish;
		$data['sum']= $sum;
		session()->put('remember_list_url',\Request::getRequestUri());
        return view('budget/index',$data);

    }
	
	public function export(Request $request)
    {
		set_time_limit(0);
		$arrayData=[];
		
		$site = $request->get('site');
		$bgbu = $request->get('bgbu');
		$sap_seller_id = $request->get('sap_seller_id');
		$level = $request->get('level');
		$sku = $request->get('sku');
	    $year_from = $request->get('year_from');
	    $year_from_arr = explode('Ver', $year_from);
		$user_id = $request->get('user_id');
		$sku_status = $request->get('sku_status');
		$b_status = $request->get('b_status');
		$where = "";
		if (Auth::user()->seller_rules) {
			$rules = explode("-",Auth::user()->seller_rules);
			if(array_get($rules,0)!='*') $where.= " and c.bg='".array_get($rules,0)."'";
			if(array_get($rules,1)!='*') $where.= " and c.bu='".array_get($rules,1)."'";
		} elseif (Auth::user()->sap_seller_id) {
			$where.= " and c.sap_seller_id=".Auth::user()->sap_seller_id;
		}

		if($bgbu){
		   $bgbu_arr = explode('_',$bgbu);
		   if(array_get($bgbu_arr,0)){
				$where.= " and c.bg='".array_get($bgbu_arr,0)."'";
		   }
		   if(array_get($bgbu_arr,1)){
				$where.= " and c.bu='".array_get($bgbu_arr,1)."'";
		   }
		}
		if($site){
			$where.= " and c.site='".$site."'";
		}
		if($user_id){
			$where.= " and c.sap_seller_id in (".implode(',',$user_id).")";
		}
		
		if($level){
			$where.= " and c.level = '".$level."'";
		}
		if($sku_status){
			
			$where.= " and c.status = '".($sku_status-1)."'";
		}
		if($b_status){
			if($b_status==1){
				$where.= " and (b.status = '".($b_status-1)."' or b.status is null)";
			}else{
				$where.= " and b.status = '".($b_status-1)."'";
			}	
		}
		if($sku){
			$search_skus = explode(',', $sku);
			$where.= " and ( sku='".$sku."' ";
			foreach($search_skus as $search_sku){
				$where.= " or sku='".trim($search_sku)."'";
			}
			$where.= ")";
		}
 		$datas = DB::select("select c.id,b.remark,a.month,c.bg,c.bu,c.sku,c.description,c.sap_seller_id,c.status,c.level,c.site,c.stock,c.cost as sku_cost,c.exception,(a.qty+a.promote_qty) as qty,a.income,a.cost,
a.common_fee,a.pick_fee,a.promotion_fee,a.storage_fee,a.amount_fee,b.status as budget_status from (select budget_id,date_format(date,'%Y%m') as month,
sum(qty) as qty,
sum(promote_qty) as promote_qty,
sum(income) as income,sum(cost) as cost,sum(common_fee) as common_fee,sum(pick_fee) as pick_fee,sum(promotion_fee) as promotion_fee,
sum(amount_fee) as amount_fee,sum(storage_fee) as storage_fee
from budget_details group by month,budget_id) as a right join (select * from budgets where year = ".$year_from_arr[0]." and quarter = ".$year_from_arr[1].") as b on a.budget_id=b.id
right join budget_skus as c on b.sku=c.sku and b.site=c.site where ((a.month>='".$year_from_arr[0]."01' and a.month<='".$year_from_arr[0]."12' ) or a.month is null) $where order by b.id,a.month asc");
		$headArray[0]='BGBU';
		$headArray[1]='SKU';
		$headArray[2]='描述';
		$headArray[3]='销售员';
		$headArray[4]='产品状态';
		$headArray[5]='站点';
		$headArray[6]='期初库存';
		$headArray[7]='不含税采购单价';
		$headArray[8]='异常率';
		
		foreach( ['销量','收入','成本','佣金','拣配费','推广费','仓储费','资金占用成本','经济效益'] as $k=>$v){
			for($i=1;$i<=13;$i++){
				$headArray[(8+$i+$k*13)] = ((($i==13)?'合计':($i.'月')).$v);
			}
		}
		$headArray[126]='备注';
		$headArray[127]='状态';
		$arrayData[] = $headArray;
		
		$emptyData = [];
		for($i=0;$i<=127;$i++){
			$emptyData[$i] = 0;
		}
		$sap_sellers = getUsers('sap_seller');
		foreach ( $datas as $data){
			$month = intval(substr($data->month,-2,2));
			if(!isset($arrayData[$data->id])){
				$arrayData[$data->id] = $emptyData;
				$arrayData[$data->id][0] = $data->bg.$data->bu;
				$arrayData[$data->id][1] = $data->sku;
				$arrayData[$data->id][2] = $data->description;
				$arrayData[$data->id][3] = array_get($sap_sellers,$data->sap_seller_id,$data->sap_seller_id);
				$arrayData[$data->id][4] = $data->status;
				$arrayData[$data->id][5] = $data->site;
				$arrayData[$data->id][6] = $data->stock;
				$arrayData[$data->id][7] = $data->sku_cost;
				$arrayData[$data->id][8] = $data->exception;
				
				$arrayData[$data->id][21] = 0;
				$arrayData[$data->id][34] = 0;
				$arrayData[$data->id][47] = 0;
				$arrayData[$data->id][60] = 0;
				$arrayData[$data->id][73] = 0;
				$arrayData[$data->id][86] = 0;
				$arrayData[$data->id][99] = 0;
				$arrayData[$data->id][112] = 0;
				$arrayData[$data->id][125] = 0;
				$arrayData[$data->id][126] = $data->remark;
				$arrayData[$data->id][127] = array_get(getBudgetStageArr(),intval($data->budget_status));
			}
			$arrayData[$data->id][$month+8] = $data->qty;
			$arrayData[$data->id][$month+21] = $data->income;
			$arrayData[$data->id][$month+34] = $data->cost;
			$arrayData[$data->id][$month+47] = $data->common_fee;
			$arrayData[$data->id][$month+60] = $data->pick_fee;
			$arrayData[$data->id][$month+73] = $data->promotion_fee;
			$arrayData[$data->id][$month+86] = $data->storage_fee;
			$arrayData[$data->id][$month+99] = $data->amount_fee;
			$arrayData[$data->id][$month+112] = round($data->income-$data->cost-$data->common_fee-$data->pick_fee-$data->promotion_fee-$data->amount_fee-$data->storage_fee,2);
			
			$arrayData[$data->id][21] += round($data->qty);
			$arrayData[$data->id][34] += round($data->income,2);
			$arrayData[$data->id][47] += round($data->cost,2);
			$arrayData[$data->id][60] += round($data->common_fee,2);
			$arrayData[$data->id][73] += round($data->pick_fee,2);
			$arrayData[$data->id][86] += round($data->promotion_fee,2);
			$arrayData[$data->id][99] += round($data->storage_fee,2);
			$arrayData[$data->id][112] += round($data->amount_fee,2);
			$arrayData[$data->id][125] += round($data->income-$data->cost-$data->common_fee-$data->pick_fee-$data->promotion_fee-$data->amount_fee-$data->storage_fee,2);
			if($month==12) ksort($arrayData[$data->id]);
		}
		if($arrayData){
			$spreadsheet = new Spreadsheet();
			$spreadsheet->getActiveSheet()->fromArray($arrayData,NULL,'A1');
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="Export_Budgets.xlsx"');
			header('Cache-Control: max-age=0');
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}
	

	public function exportSku(Request $request)
    {
		set_time_limit(0);
		$arrayData=[];
		$budget_id = intval($request->get('budget_id'));
		$budget = Budgets::find($budget_id);
		if(empty($budget)) die;
 		$datas =  Budgetdetails::selectRaw('weeks,any_value(ranking) as ranking,any_value(price) as price,sum(qty) as qty,any_value(promote_price) as promote_price,sum(promote_qty) as promote_qty,any_value(promotion) as promotion,any_value(exception) as exception')->where('budget_id',$budget_id)->groupBy('weeks')->orderBy('weeks','asc')->get()->keyBy('weeks')->toArray();
		$arrayData[][0]='预算导出表格，可以直接修改数值用于导入';
		$arrayData[] = ['0'=>'周','1'=>'日期','2'=>'排名目标','3'=>'正常售价（外币','4'=>'正常销量','5'=>'促销价（外币）','6'=>'促销销量','7'=>'推广费率','8'=>'异常率'];
		
		$weeks = date("W", mktime(0, 0, 0, 12, 28, $budget->year));

		for($i=1;$i<=$weeks;$i++){
			$arrayData[] = ['0'=>$i,'1'=>date("Ymd", strtotime($budget->year . 'W' . sprintf("%02d",$i))).'-'.date("Ymd", strtotime($budget->year . 'W' . sprintf("%02d",$i))+86400*6),'2'=>array_get($datas,$i.'.ranking'),'3'=>array_get($datas,$i.'.price'),'4'=>array_get($datas,$i.'.qty'),'5'=>array_get($datas,$i.'.promote_price'),'6'=>array_get($datas,$i.'.promote_qty'),'7'=>array_get($datas,$i.'.promotion'),'8'=>array_get($datas,$i.'.exception')];
		}
		if($arrayData){
			$spreadsheet = new Spreadsheet();
			$spreadsheet->getActiveSheet()->fromArray($arrayData,NULL,'A1');
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="Export_Budget_Sku.xlsx"');
			header('Cache-Control: max-age=0');
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}
	
	public function edit(Request $request)
    {	
		if(!Auth::user()->can(['budgets-show'])) die('Permission denied -- budgets-show');
		$sku = $request->get('sku');
		$site = $request->get('site');
		$year = intval($request->get('year'));
		$quarter = intval($request->get('quarter'));
		if($quarter>4) $quarter=4;
		if($quarter<1) $quarter=1;
		$showtype=$request->get('showtype');
		$sku_base_data = Budgetskus::where('sku',$sku)->where('site',$site)->first();
		if(empty($sku_base_data)) die('没有该SKU对应预算基础信息，请联系管理员新增！');
		$cur = 'EUR';
		if($site=='www.amazon.com') $cur = 'USD';
		if($site=='www.amazon.ca') $cur = 'CAD';
		if($site=='www.amazon.co.uk') $cur = 'GBP';
		if($site=='www.amazon.co.jp') $cur = 'JPY';
		$budget = Budgets::firstOrCreate(['sku'=>$sku,'site'=>$site,'year'=>$year,'quarter'=>$quarter],[
			'then_status'=>$sku_base_data->status,
			'then_level'=>$sku_base_data->level,
			'then_stock'=>$sku_base_data->stock,
			'then_volume'=>$sku_base_data->volume,
			'then_size'=>$sku_base_data->size,
			'then_cost'=>$sku_base_data->cost,
			'then_common_fee'=>$sku_base_data->common_fee,
			'then_pick_fee'=>$sku_base_data->pick_fee,
			'then_exception'=>$sku_base_data->exception,
			'then_bg'=>$sku_base_data->bg,
			'then_bu'=>$sku_base_data->bu,
			'then_description'=>$sku_base_data->description,
			'then_sap_seller_id'=>$sku_base_data->sap_seller_id
		]);
		$budget_id = $budget->id;
		$data['sku']=$sku;
		$data['site']=$site;
		$data['year']=$year;
		$data['quarter']=$quarter;
		$data['budget_id']=$budget_id;
		$data['budget']=$budget;
		//如果没有预算默认导入本年上版本预算
		$exists_count = Budgetdetails::where('budget_id',$budget_id)->count();
		if(!$exists_count){
			$pre_budget_id = Budgets::where('sku',$sku)->where('site',$site)->where('year',$year)->where('quarter',($quarter-1))->where('status','>',0)->value('id');
			if($pre_budget_id){
				$undate = '03-31';	
				if($quarter==2) $undate = '03-31';
				if($quarter==3) $undate = '06-30';
				if($quarter==4) $undate = '09-30';
				$pre_budget_datas= Budgetdetails::selectRaw("$budget_id as budget_id,weeks,date,ranking,price,qty,promote_price, promote_qty, promotion, exception,income,cost,common_fee,pick_fee,promotion_fee,amount_fee,storage_fee,created_at,updated_at")->where('budget_id',$pre_budget_id)->where('date','<=',$year.'-'.$undate)->get()->toArray();
				if($pre_budget_datas) Budgetdetails::insertOnDuplicateWithDeadlockCatching(array_values($pre_budget_datas), ['ranking','price','qty','promote_price', 'promote_qty', 'promotion', 'exception','income','cost','common_fee','pick_fee','promotion_fee','amount_fee','storage_fee','created_at','updated_at']);

				$pre_budget_datas= Budgetdetails::selectRaw("$budget_id as budget_id,weeks,date,ranking,price,qty,promote_price, promote_qty, promotion, exception,created_at,updated_at")->where('budget_id',$pre_budget_id)->where('date','>',$year.'-'.$undate)->get()->toArray();
				if($pre_budget_datas) Budgetdetails::insertOnDuplicateWithDeadlockCatching(array_values($pre_budget_datas), ['ranking','price','qty','promote_price', 'promote_qty', 'promotion', 'exception','created_at','updated_at']);
			}
			

		}
		


		if($budget->status==0 && $showtype){
			$request->session()->flash('error_message','需要在周视图填写提交后才可切换视图！');
			return redirect('budgets/edit?sku='.$budget->sku.'&site='.$budget->site.'&year='.$budget->year.'&quarter='.$budget->quarter);
		}	
		if($showtype=='seasons' || $showtype=='months'){
			$data['datas'] = Budgetdetails::selectRaw("date_format(date,'%Y%m') as month,any_value(ranking) as ranking,sum(price*qty)/sum(qty) as price,sum(qty) as qty,sum(promote_price*promote_qty)/sum(promote_qty) as promote_price,sum(promote_qty) as promote_qty,sum(promotion_fee)/sum(income) as promotion,sum(income) as income,sum(cost) as cost,sum(common_fee) as common_fee,sum(pick_fee) as pick_fee,sum(promotion_fee) as promotion_fee,sum(amount_fee) as amount_fee,sum(storage_fee) as storage_fee,sum((price*qty+promote_price*promote_qty)*exception) as exception_fee")->where('budget_id',$budget_id)->whereRaw("left(date,4)=$year")->groupBy('month')->get()->keyBy('month')->toArray();
			if($showtype=='seasons'){
				$i=1;
				$type_datas=[];
				foreach($data['datas'] as $k=>$v){
					if($k<=($year.'03')) $i=1;
					if($k<=($year.'06') && $k>=($year.'04')) $i=2;
					if($k<=($year.'09') && $k>=($year.'07')) $i=3;
					if($k>=($year.'10')) $i=4;
					if(!isset($type_datas[$i]['amount_n'])) $type_datas[$i]['amount_n']=0 ;
					if(!isset($type_datas[$i]['qty'])) $type_datas[$i]['qty']=0 ;
					if(!isset($type_datas[$i]['amount_p'])) $type_datas[$i]['amount_p']=0 ;
					if(!isset($type_datas[$i]['exception_fee'])) $type_datas[$i]['exception_fee']=0 ;
					if(!isset($type_datas[$i]['promote_qty'])) $type_datas[$i]['promote_qty']=0;
					if(!isset($type_datas[$i]['income'])) $type_datas[$i]['income']=0 ;
					if(!isset($type_datas[$i]['cost'])) $type_datas[$i]['cost']=0 ;
					if(!isset($type_datas[$i]['common_fee'])) $type_datas[$i]['common_fee']=0 ;
					if(!isset($type_datas[$i]['pick_fee'])) $type_datas[$i]['pick_fee']=0 ;
					if(!isset($type_datas[$i]['promotion_fee'])) $type_datas[$i]['promotion_fee']=0 ;
					if(!isset($type_datas[$i]['amount_fee'])) $type_datas[$i]['amount_fee']=0 ;
					if(!isset($type_datas[$i]['storage_fee'])) $type_datas[$i]['storage_fee']=0 ;
					$type_datas[$i]['ranking']=$v['ranking'];
					$type_datas[$i]['amount_n']+=$v['price']*$v['qty'];
					$type_datas[$i]['qty']+=$v['qty'];
					$type_datas[$i]['exception_fee']+=$v['exception_fee'];
					$type_datas[$i]['amount_p']+=$v['promote_price']*$v['promote_qty'];
					$type_datas[$i]['promote_qty']+=$v['promote_qty'];
					$type_datas[$i]['income']+=$v['income'];
					$type_datas[$i]['cost']+=$v['cost'];
					$type_datas[$i]['common_fee']+=$v['common_fee'];
					$type_datas[$i]['pick_fee']+=$v['pick_fee'];
					$type_datas[$i]['promotion_fee']+=$v['promotion_fee'];
					$type_datas[$i]['amount_fee']+=$v['amount_fee'];
					$type_datas[$i]['storage_fee']+=$v['storage_fee'];
				}
				unset($data['datas']);
				$data['datas']=$type_datas;
			}
			
			
		}elseif($showtype=='days'){
			$data['datas']= Budgetdetails::where('budget_id',$budget_id)->whereRaw("left(date,4)=$year")->orderBy('date','asc')->get()->toArray();
		}else{
			$data['datas']= Budgetdetails::selectRaw('weeks,any_value(ranking) as ranking,any_value(price) as price,sum(qty) as qty,any_value(promote_price) as promote_price,sum(promote_qty) as promote_qty,any_value(promotion) as promotion,any_value(exception) as exception,sum(income) as income,sum(cost) as cost,sum(common_fee) as common_fee,sum(pick_fee) as pick_fee,sum(promotion_fee) as promotion_fee,sum(amount_fee) as amount_fee,sum(storage_fee) as storage_fee')->where('budget_id',$budget_id)->groupBy('weeks')->get()->keyBy('weeks')->toArray();
		}
		$data['showtype'] = $showtype;
		$data['base_data']= $budget->toArray();
		$data['rate']= array_get(DB::table('cur_rate')->pluck('rate','cur'),$cur,0);
		
		$data['site_code'] = strtoupper(substr($site,-2));
		if($data['site_code']=='OM') $data['site_code']='US';
		
		$storage_fee = json_decode(json_encode(DB::table('storage_fee')->where('type','FBA')->where('site',$data['site_code'])->where('size',$data['base_data']['then_size'])->first()),true);

		$tax_rate=DB::table('tax_rate')->where('site',$data['site_code'])->whereIn('sku',array('OTHERSKU',$sku))->pluck('tax','sku');
		$data['base_data']['tax']= round(((array_get($tax_rate,$sku)??array_get($tax_rate,'OTHERSKU'))??0),4);
		$shipfee = (array_get(getShipRate(),$data['site_code'].'.'.$sku)??array_get(getShipRate(),$data['site_code'].'.default'))??0;
		$data['base_data']['headshipfee']=round($data['base_data']['then_volume']/1000000*1.2*round($shipfee,4),2);
		$data['base_data']['cold_storagefee']=round(array_get($storage_fee,'2_10_fee',0)*$data['base_data']['then_volume']/1000000/(($year==2020 && $quarter==1)?8:3),4);
		$data['base_data']['hot_storagefee']=round(array_get($storage_fee,'11_1_fee',0)*$data['base_data']['then_volume']/1000000/(($year==2020 && $quarter==1)?8:3),4);
		$data['remember_list_url'] = session()->get('remember_list_url');
		return view('budget/edit',$data);
    }
	
	
	public function upload( Request $request )
	{	
		if(!Auth::user()->can(['budgets-show'])) die('Permission denied -- budgets-show');
		$budget_id = intval($request->get('budget_id'));
		$budget = Budgets::find($budget_id);
		if($budget->status!=0) die('预算已经提交或确认！');
		if(empty($budget)) die;
		$undate = '0000-00-00';	
		if($budget->quarter==2) $undate = $budget->year.'-03-31';
		if($budget->quarter==3) $undate = $budget->year.'-06-30';
		if($budget->quarter==4) $undate = $budget->year.'-09-30';
		if($request->isMethod('POST')){  
            $file = $request->file('importFile');  
  			if($file){
				if($file->isValid()){  
					$originalName = $file->getClientOriginalName();  
					$ext = $file->getClientOriginalExtension();  
					$type = $file->getClientMimeType();  
					$realPath = $file->getRealPath();  
					$newname = date('Y-m-d-H-i-S').'-'.uniqid().'.'.$ext;  
					$newpath = '/uploads/BudgetsUpload/'.date('Ymd').'/';
					$inputFileName = public_path().$newpath.$newname;
					$bool = $file->move(public_path().$newpath,$newname);
					if($bool){
						$weeks = date("W", mktime(0, 0, 0, 12, 28, $budget->year));
						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
						$week_per = $this->week_per;		
						$updateData=[];
						foreach($importData as $key => $data){
							if($key>2 && $key<($weeks+3)){
								foreach(['C'=>'ranking','D'=>'price','E'=>'qty','F'=>'promote_price','G'=>'promote_qty','H'=>'promotion','I'=>'exception'] as $temp_k=>$temp_v){
									$max_value=0;
									$week_value = array_get($data,$temp_k,NULL);
									if($temp_k == 'E' || $temp_k == 'G') $week_value=intval($week_value);
									if($temp_k == 'D' || $temp_k == 'F' || $temp_k == 'H' || $temp_k == 'I') $week_value=round($week_value,4);
									for($k=0;$k<=6;$k++){
										$date = date("Y-m-d", strtotime($budget->year . 'W' . sprintf("%02d",($key-2)))+86400*$k);
										if($temp_v=='qty' || $temp_v == 'promote_qty'){
											$value = round($week_value*array_get($week_per,$k));			
											if($max_value+$value>$week_value) $value = $week_value-$max_value;
											if($max_value<=$week_value && $k==6) $value = $week_value-$max_value;
											$max_value+=$value;
										}else{
											$value = ($temp_v=='ranking')?$week_value:round($week_value,2);
										}
										if($date>$undate){
											$updateData[$date]['budget_id']=$budget_id;
											$updateData[$date]['weeks']=($key-2);
											$updateData[$date]['date']=$date;
											$updateData[$date][$temp_v]=$value;
											$updateData[$date]['created_at']=$updateData[$date]['updated_at']=date('Y-m-d H:i:s');
										}
									}
								}
							}
						}
						if($updateData) Budgetdetails::insertOnDuplicateWithDeadlockCatching(array_values($updateData), ['ranking','price','qty','promote_price', 'promote_qty', 'promotion', 'exception','created_at','updated_at']);
						$request->session()->flash('success_message','Import Success!');
					}else{
						$request->session()->flash('error_message','UploadFailed');
					}          
				} 
			}else{
				$request->session()->flash('error_message','Please Select Upload File');
			} 
        } 
		return redirect('budgets/edit?sku='.$budget->sku.'&site='.$budget->site.'&year='.$budget->year.'&quarter='.$budget->quarter);
	
	}
	

    public function update(Request $request)
    {
		if(!Auth::user()->can(['budgets-show'])) die('Permission denied -- budgets-show');
		$name = $request->get('name');
		$data = explode("-",$name);
		$budget_id = intval(array_get($data,0));
		$budget = Budgets::find($budget_id);
		if(empty($budget)) die;

		$undate = '0000-00-00';	
		if($budget->quarter==2) $undate = $budget->year.'-03-31';
		if($budget->quarter==3) $undate = $budget->year.'-06-30';
		if($budget->quarter==4) $undate = $budget->year.'-09-30';

		$week_per = $this->week_per;
		
		if(!is_numeric(array_get($data,1))){
			if(array_get($data,1)=='status' || array_get($data,1)=='remark'){
				$budget->{array_get($data,1)} = $request->get('value');
				
				if(array_get($data,1)=='status' && $request->get('value')==1){
					$weeks = date("W", mktime(0, 0, 0, 12, 28, $budget->year));
					$updateData = [];
					for($i=1;$i<=$weeks;$i++){
						$data = explode('|',$request->get($i.'-week_line_data'));
						if(count($data)!=7) continue;
						
						for($j=0;$j<=6;$j++){
							$max_value=0;
							$week_value = $data[$j];
							if($j==0) $field = 'income';
							if($j==1) $field = 'cost';
							if($j==2) $field = 'common_fee';
							if($j==3) $field = 'pick_fee';
							if($j==4) $field = 'promotion_fee';
							if($j==5) $field = 'amount_fee';
							if($j==6) $field = 'storage_fee';
							for($k=0;$k<=6;$k++){
								$date = date("Y-m-d", strtotime($budget->year . 'W' . sprintf("%02d",$i))+86400*$k);
								$value = round($week_value*array_get($week_per,$k),2);
								if($max_value+$value>$week_value) $value = $week_value-$max_value;
								if($max_value<=$week_value && $k==6) $value = $week_value-$max_value;
								$max_value+=$value;

								if($date>$undate){
									$updateData[$date]['budget_id']=$budget_id;
									$updateData[$date]['weeks']=$i;
									$updateData[$date]['date']=$date;
									$updateData[$date][$field]=$value;
									$updateData[$date]['created_at']=$updateData[$date]['updated_at']=date('Y-m-d H:i:s');
								}
							}
						}
					}
					if($updateData) Budgetdetails::insertOnDuplicateWithDeadlockCatching(array_values($updateData), ['income','cost', 'common_fee', 'pick_fee', 'promotion_fee', 'amount_fee', 'storage_fee','created_at','updated_at']);
					$budget->qty = round($request->get('total_qty'));
					$budget->income = round($request->get('total_income'),2);
					$budget->cost = round($request->get('total_cost'),2);
					$budget->common_fee = round($request->get('total_commonfee'),2);
					$budget->pick_fee = round($request->get('total_pickfee'),2);
					$budget->promotion_fee = round($request->get('total_profee'),2);
					$budget->amount_fee = round($request->get('total_amountfee'),2);
					$budget->storage_fee = round($request->get('total_storagefee'),2);
				}
				$budget->save();
				echo $budget->status;
				die();
			}else{
				$field_rate = (array_get($data,1)=='exception' || array_get($data,1)=='common_fee')?0.01:1;
				$budget->{'then_'.array_get($data,1)} = $request->get('value')*$field_rate;	
				$budget->save();
				$return[$request->get('name')]=round($request->get('value'),4);
				echo json_encode($return);
				die();
			}
		}
		if(intval(array_get($data,1))>0){
			$week = array_get($data,1);
			if(in_array(array_get($data,2),['qty','promote_qty'])){
				$week_value = round($request->get('value'));
			}elseif(array_get($data,2)=='promotion' || array_get($data,2)=='exception'){
				$week_value = round($request->get('value'),2)/100;
			}else{
				$week_value = $request->get('value');
			}
			$max_value=0;
			for($i=0;$i<=6;$i++){
				$date = date("Y-m-d", strtotime($budget->year . 'W' . sprintf("%02d",$week))+86400*$i);
				if(in_array(array_get($data,2),['qty','promote_qty'])){
					$value = round($week_value*array_get($week_per,$i));
					if($max_value+$value>$week_value) $value = $week_value-$max_value;
					if($max_value<=$week_value && $i==6) $value = $week_value-$max_value;
					$max_value+=$value;
				}else{
					$value = $week_value;
				}
				if($date>$undate){
					Budgetdetails::updateOrCreate(
						['budget_id' => $budget_id,'weeks' => array_get($data,1),'date'=>$date],
						[array_get($data,2)=>$value]
					);
				}
			}
			
			if(array_get($data,2)!='ranking'){
				$return[$request->get('name')]=round($request->get('value'),4);
				echo json_encode($return);
				die();
			}
		}		
    }


	/*
	* 添加新品的sku
	*/
	public function create(Request $request)
	{
		// if(!Auth::user()->can(['budgets-add'])) die('Permission denied -- budgets-add');
		if($_POST){
			$userData = Auth::user();
			$item_group = $request->get('item_group');
			$site = $request->get('site');
			//退货率和关税税率在页面上的时候会输入%，但是存入数据库的时候不要把%存进去
			$exception = explode('%',$request->get('exception'))[0]/100;
			$tax = explode('%',$request->get('tax'))[0]/100;
			$common_fee = explode('%',$request->get('common_fee'))[0]/100;

			//添加的新品的sku编码为item_group20200001
			$year = date('Y');
			$year = $year > 2020 ? $year : 2020;
			$groupYear = $item_group.$year;
			$itemSku = Budgetskus::where('sku','like',$groupYear.'%')->orderBy('sku','desc')->first();

			//item_group原先有添加新品的时候，sku编码为在原先的编码基础上+1,item_group原先没有添加新品的时候，sku编码为item_group20200001
			$sku = empty($itemSku) ? $groupYear.'0001' : $groupYear.sprintf("%04d",explode($groupYear,$itemSku->sku)[1] + 1);
			DB::beginTransaction();
			$res = Budgetskus::updateOrCreate(
				['sku' => $sku,'site' => $site],
				[
					'sku' => $sku,
					'description' => $request->get('description'),
					'site' => $site,
					'status' => 99,
					// 'level' => 0,
					// 'stock' => 0,
					'volume' => $request->get('volume'),
					// 'size' => 0,
					'cost' => $request->get('cost'),
					'common_fee' => $common_fee,
					'pick_fee' => $request->get('pick_fee'),
					'exception' => $exception,
					'bg' => $userData->ubg,
					'bu' => $userData->ubu,
					'sap_seller_id' => $userData->sap_seller_id,
				]
			);
			if(empty($res)){
				DB::rollBack();
				$request->session()->flash('error_message','Save budget-skus Failed! Please resubmit!');
				return redirect('budgets');
			}else{
				$siteShort = getSiteShort();
				$res = TaxRate::updateOrCreate(
					['sku' => $sku,'site' => $site],
					[
						'sku' => $sku,
						'site' => isset($siteShort[$site]) ? strtoupper($siteShort[$site]) : $site,
						'tax' => $tax,
					]
				);
				if(empty($res)){
					DB::rollBack();
					$request->session()->flash('error_message','Save tax-rate Failed! Please resubmit!');
					return redirect('budgets');
				}
			}
			DB::commit();
			$request->session()->flash('success_message','Add New Success');
			return redirect('budgets');
		}else{
			$itemGroup  = $this->getItemGroup();
			return view('budget/addNew',array('itemGroup'=>$itemGroup));
		}
	}

}