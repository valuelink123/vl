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
		select budget_skus.*,budgets_1.qty as qty1,budgets_1.amount as amount1,budgets_1.profit as profit1,budgets_1.status as budget_status,budgets_1.remark
,budgets_2.qty as qty2,budgets_2.amount as amount2,budgets_2.profit as profit2 from budget_skus 
		left join (select * from budgets where year = '$year') as budgets_1 
		on budget_skus.sku = budgets_1.sku and budget_skus.site = budgets_1.site
		left join (select * from budgets where year = '$year-1') as budgets_2 
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
			
		$tax_rate=DB::table('tax_rate')->where('site',$data['site_code'])->whereIn('sku',array('OTHERSKU',$sku))->pluck('tax','sku');
		$data['base_data']['tax']= round(((array_get($tax_rate,$sku)??array_get($tax_rate,'OTHERSKU'))??0),4);
		$shipfee = (array_get(getShipRate(),$data['site_code'].'.'.$sku)??array_get(getShipRate(),$data['site_code'].'.default'))??0;
		$data['base_data']['headshipfee']=round($data['base_data']['volume']/1000000*round($shipfee,4),2);
		return view('budget/edit',$data);
    }
	

    public function update(Request $request)
    {
		$name = $request->get('name');
		$data = explode("-",$name);
		$budget_id = intval(array_get($data,0));
		$budget = Budgets::find($budget_id);
		if(empty($budget)) die;
		
		if(!is_numeric(array_get($data,1))){
			if(array_get($data,1)=='status' || array_get($data,1)=='remark'){
				$budget->{array_get($data,1)} = $request->get('value');
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
			
			$week_per = ['0'=>1.13/7.01,'1'=>1.12/7.01,'2'=>1.09/7.01,'3'=>1.04/7.01,'4'=>0.91/7.01,'5'=>0.86/7.01,'6'=>0.86/7.01];
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
					['budget_id' => array_get($data,0),'weeks' => array_get($data,1),'date'=>$date],
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