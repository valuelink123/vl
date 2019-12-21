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
			$where.= " and level = '".(($level=='S')?0:$level)."'";
		}
		
		if($sku){
			$where.= " (sku='".$sku."' or description like '%".$sku."%')";
		}
		
		
		$sql = "(
		select budget_skus.*,budgets_1.qty as qty1,budgets_1.income as amount1,(budgets_1.income-budgets_1.cost) as profit1,(budgets_1.income-budgets_1.cost-budgets_1.common_fee-budgets_1.pick_fee-budgets_1.promotion_fee-budgets_1.amount_fee-budgets_1.storage_fee) as economic1,budgets_1.status as budget_status,budgets_1.remark
,budgets_2.qty as qty2,budgets_2.income as amount2,(budgets_2.income-budgets_2.cost) as profit2,(budgets_2.income-budgets_2.cost-budgets_2.common_fee-budgets_2.pick_fee-budgets_2.promotion_fee-budgets_2.amount_fee-budgets_2.storage_fee) as economic2 from budget_skus 
		left join (select * from budgets where year = $year) as budgets_1 
		on budget_skus.sku = budgets_1.sku and budget_skus.site = budgets_1.site
		left join (select * from budgets where year = ".($year-1).") as budgets_2 
		on budget_skus.sku = budgets_2.sku and budget_skus.site = budgets_2.site
		) as sku_tmp_cc";
 		$datas = DB::table(DB::raw($sql))->whereRaw($where)->paginate(20);
		
        $data['teams']= getUsers('sap_bgbu');
		$data['users']= getUsers('sap_seller');
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
		$base_data = Budgetskus::where('sku',$sku)->where('site',$site)->first();
		if(empty($base_data)) die('没有该SKU对应预算基础信息，请联系管理员新增！');
		
		
		
		$cur = 'EUR';
		if($site=='www.amazon.com') $cur = 'USD';
		if($site=='www.amazon.ca') $cur = 'CAD';
		if($site=='www.amazon.co.uk') $cur = 'GBP';
		if($site=='www.amazon.co.jp') $cur = 'JPY';
		$budget = Budgets::firstOrCreate(['sku'=>$sku,'site'=>$site,'year'=>$year]);
		$budget_id = $budget->id;
		$data['sku']=$sku;
		$data['site']=$site;
		$data['year']=$year;
		$data['budget_id']=$budget_id;
		$data['budget']=$budget;
		$data['datas']= Budgetdetails::selectRaw('weeks,any_value(ranking) as ranking,any_value(price) as price,sum(qty) as qty,any_value(promote_price) as promote_price,sum(promote_qty) as promote_qty,any_value(promotion) as promotion')->where('budget_id',$budget_id)->groupBy('weeks')->get()->keyBy('weeks')->toArray();
		$data['base_data']= $base_data->toArray();
		$data['rate']= array_get(DB::table('cur_rate')->pluck('rate','cur'),$cur,0);
		
		$data['site_code'] = strtoupper(substr($site,-2));
		if($data['site_code']=='OM') $data['site_code']='US';
		
		$storage_fee = json_decode(json_encode(DB::table('storage_fee')->where('type','FBA')->where('site',$data['site_code'])->where('size',$data['base_data']['size'])->first()),true);

		
		$tax_rate=DB::table('tax_rate')->where('site',$data['site_code'])->whereIn('sku',array('OTHERSKU',$sku))->pluck('tax','sku');
		$data['base_data']['tax']= round(((array_get($tax_rate,$sku)??array_get($tax_rate,'OTHERSKU'))??0),4);
		$shipfee = (array_get(getShipRate(),$data['site_code'].'.'.$sku)??array_get(getShipRate(),$data['site_code'].'.default'))??0;
		$data['base_data']['headshipfee']=round($data['base_data']['volume']/1000000*round($shipfee,4),2);
		$data['base_data']['cold_storagefee']=round(array_get($storage_fee,'2_10_fee',0)*$data['base_data']['volume']/1000000/8,4);
		$data['base_data']['hot_storagefee']=round(array_get($storage_fee,'11_1_fee',0)*$data['base_data']['volume']/1000000/8,4);
		return view('budget/edit',$data);
    }
	

    public function update(Request $request)
    {
		$name = $request->get('name');
		$data = explode("-",$name);
		$budget_id = intval(array_get($data,0));
		$budget = Budgets::find($budget_id);
		if(empty($budget)) die;
		$week_per = ['0'=>1.13/7.01,'1'=>1.12/7.01,'2'=>1.09/7.01,'3'=>1.04/7.01,'4'=>0.91/7.01,'5'=>0.86/7.01,'6'=>0.86/7.01];
		
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
								$value = ($field=='qty' || $field == 'promote_qty')?round($week_value*array_get($week_per,$k)):round($week_value*array_get($week_per,$k),2);
								if($max_value+$value>$week_value) $value = $week_value-$max_value;
								if($max_value<=$week_value && $i==6) $value = $week_value-$max_value;
								$max_value+=$value;
								$updateData[$date]['budget_id']=$budget_id;
								$updateData[$date]['weeks']=$i;
								$updateData[$date]['date']=$date;
								$updateData[$date][$field]=$value;
							}
						}
					}
					if($updateData) Budgetdetails::insertOnDuplicateWithDeadlockCatching(array_values($updateData), ['qty','promote_qty','income','cost', 'common_fee', 'pick_fee', 'promotion_fee', 'amount_fee', 'storage_fee']);
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
				die();
			}else{
				Budgetskus::where('sku',$budget->sku)->where('site',$budget->site)->update([array_get($data,1)=>$request->get('value')]);	
				$return[$request->get('name')]=round($request->get('value'),4);
				echo json_encode($return);
				die();
			}
		}
		if(intval(array_get($data,1))>0){
			$week = array_get($data,1);
			
			
			if(in_array(array_get($data,2),['qty','promote_qty'])){
				$week_value = round($request->get('value'));
			}elseif(array_get($data,2)=='promotion'){
				$week_value = round($request->get('value'),4);
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
				Budgetdetails::updateOrCreate(
					['budget_id' => $budget_id,'weeks' => array_get($data,1),'date'=>$date],
					[array_get($data,2)=>$value]
				);
			}
			
			if(array_get($data,2)!='ranking'){
				$return[$request->get('name')]=round($request->get('value'),4);
				echo json_encode($return);
				die();
			}
		}		
    }



}