<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Accounts;
use App\User;
use App\Group;
use App\AsinSalesPlan;
use App\ShipmentRequest;
use App\SapPurchase;
use App\DailyStatistic;
use App\SapPurchaseRecord;
use App\SapSkuSite;
use App\InternationalTransportTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;
class MrpController extends Controller
{

	use \App\Traits\Mysqli;
	use \App\Traits\DataTables;
    /**
     * Create a new controller instance.
     *
     * @return void
     *
     */
	public $week_per = ['0'=>1.13/7.01,'1'=>1.12/7.01,'2'=>1.09/7.01,'3'=>1.04/7.01,'4'=>0.91/7.01,'5'=>0.86/7.01,'6'=>0.86/7.01];
 
    public function __construct()
    {
        $this->middleware('auth');
		parent::__construct();
    }

   	public function index(Request $req)
    {
		$search = isset($_POST['search']) ? $req->get('search') : '';
		$search = $this->getSearchData(explode('&',$search));
		
		$date = (intval(array_get($search,'date'))>0)?intval(array_get($search,'date')):90;
		if ($req->isMethod('GET')) {
			return view('mrp/index', ['date' => $date,'bgs'=>$this->getBgs(),'bus'=>$this->getBus()]);
		}
		$searchField = array('bg'=>'a.bg','bu'=>'a.bu','site'=>'a.marketplace_id','sku'=>'a.sku','sku_level'=>'a.status','sku_level'=>'a.status','sap_seller_id'=>'a.sap_seller_id');
		
		
		$where = $this->getSearchWhereSql($search,$searchField);

		if(array_get($search,'keyword')){
			$where .=" and (a.asin='".array_get($search,'keyword')."' or a.sku='".array_get($search,'keyword')."')";
		}
		
		if(isset($search['sku_status'])){
			$where .=" and a.sku_status in (".array_get($search,'sku_status').")";
		}
		
		if(array_get($search,'stockkeep_from')){
			$where .=" and ((afn_sellable+afn_reserved+mfn_sellable+sum_estimated_afn)/(sales_4_weeks/28*0.5+sales_2_weeks/14*0.3+sales_1_weeks/7*0.2))>=".intval(array_get($search,'stockkeep_from'));
		}
		if(array_get($search,'stockkeep_to')){
			$where .=" and ((afn_sellable+afn_reserved+mfn_sellable+sum_estimated_afn)/(sales_4_weeks/28*0.5+sales_2_weeks/14*0.3+sales_1_weeks/7*0.2))<=".intval(array_get($search,'stockkeep_to'));
		}
		if(array_get($search,'outstock_from')){
			$where .=" and (out_stock_count>=".intval(array_get($search,'outstock_from')).")";
		}
		if(array_get($search,'outstock_to')){
			$where .=" and (out_stock_count<=".intval(array_get($search,'outstock_to')).")";
		}
		if(array_get($search,'stock_status')){
			if(array_get($search,'stock_status')==1) $where .=" and out_stock_count>0";
			if(array_get($search,'stock_status')==2) $where .=" and over_stock_count>0";
			if(array_get($search,'stock_status')==3) $where .=" and (out_stock_count>0 or over_stock_count>0)";
			
		}
		$seller_permissions = $this->getUserSellerPermissions();
		foreach($seller_permissions as $key=>$val){
			if($key=='bg' && $val) $where .=" and a.bg='$val'";
			if($key=='bu' && $val) $where .=" and a.bu='$val'";
			if($key=='sap_seller_id' && $val) $where .=" and a.sap_seller_id='$val'";
		}
		$orderby = $this->dtOrderBy($req);
		$date_to = date('Y-m-d',strtotime('+'.$date.'days'));
		$date_from = date('Y-m-d');
		//开始时间为date_from的周一日期，结束时间为date_to的周天日期(查询数据的开始时间和结束时间)
		$date_start = date("Y-m-d", strtotime('monday this week', strtotime($date_from)));
		$date_end = date("Y-m-d", strtotime('sunday this week', strtotime($date_to)));

		$sql = $this->getSql($where,$date_start,$date_end,$orderby);
		if($req['length'] != '-1'){
			$limit = $this->dtLimit($req);
			$sql .= " LIMIT {$limit} ";
		}
		$data = DB::connection('amazon')->select($sql);
		$data = $this->getReturnData(json_decode(json_encode($data),true));
		$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
		return compact('data', 'recordsTotal', 'recordsFiltered');
    }
	
	
	public function list(Request $req)
    {
    	if(!Auth::user()->can(['sales-forecast-show'])) die('Permission denied -- sales-forecast-show');
		$updateRole = Auth::user()->can(['sales-forecast-update']) ? 1 : 0;//是否有更新数据的权限
		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$date = array_get($search,'date')??date('Y-m-d');
		if ($req->isMethod('GET')) {
			return view('mrp/list', ['date'=>$date,'bgs'=>$this->getBgs(),'bus'=>$this->getBus(),'weekDate'=>$this->get22WeekDate($date)]);
		}

		$date_from = date('Y-m-d',strtotime($date.' next monday'));
		$date_to = date('Y-m-d',strtotime($date.' +22 weeks sunday'));
		$searchField = array('bg'=>'a.bg','bu'=>'a.bu','site'=>'a.marketplace_id','sku'=>'a.sku','sku_level'=>'a.status','sku_level'=>'a.status','sap_seller_id'=>'a.sap_seller_id');
		
		$where = $this->getSearchWhereSql($search,$searchField);

		$type = array_get($search,'type');
		if($type!='sku') $type='asin';
		
		if(array_get($search,'keyword')){
			$where .=" and (a.asin='".array_get($search,'keyword')."' or a.sku='".array_get($search,'keyword')."')";
		}
		
		if(isset($search['sku_status'])){
			$where .=" and a.sku_status in (".array_get($search,'sku_status').")";
		}
		
		$seller_permissions = $this->getUserSellerPermissions();
		foreach($seller_permissions as $key=>$val){
			if($key=='bg' && $val) $where .=" and a.bg='$val'";
			if($key=='bu' && $val) $where .=" and a.bu='$val'";
			if($key=='sap_seller_id' && $val) $where .=" and a.sap_seller_id='$val'";
		}
		$orderby = $this->dtOrderBy($req);
		
		$sql = $this->getSql($where,$date_from,$date_to,$orderby,false);
			
		if($type=='sku'){
			$sql="SELECT SQL_CALC_FOUND_ROWS sku,marketplace_id,any_value(sap_seller_id) as sap_seller_id,  count(asin) as asin, sum(daily_sales) as daily_sales,any_value(min_purchase_quantity) as min_purchase_quantity,sum(afn_sellable) as afn_sellable,sum(afn_reserved) as afn_reserved,any_value(mfn_sellable) as mfn_sellable,any_value(sz_sellable) as sz_sellable from (".str_replace('SQL_CALC_FOUND_ROWS','',$sql).") as skus_table 
			group by sku,marketplace_id order by daily_sales desc";
		}

		if($req['length'] != '-1'){
			$limit = $this->dtLimit($req);
			$sql .= " LIMIT {$limit} ";
		}
		$datas = DB::connection('amazon')->select($sql);
		
		$datas = json_decode(json_encode($datas),true);
		$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
		$data = [];
		$siteCode = array_flip(getSiteCode());
		$sellers = getUsers('sap_seller');
		$sap_seller_id = Auth::user()->sap_seller_id;
		foreach ($datas as $key => $val) {
			//$min_purchase_quantity = intval(SapPurchaseRecord::where('sku',$val['sku'])->where('sap_factory_code','<>','')->whereNotIn('supplier',['CN01','WH01','HK03'])->orderBy('created_date','desc')->value('min_purchase_quantity'));
			$data_placement ='top';
			if($key<4) $data_placement='bottom';
			$asin_plans = AsinSalesPlan::SelectRaw('sum(quantity_last) as quantity,week_date,any_value(status) as status')->where($type,$val[$type])->where('marketplace_id',$val['marketplace_id'])->where('week_date','>=',$date)->where('week_date','<=',$date_to)->groupBy(['week_date'])->get()->keyBy('week_date')->toArray();

			$data[$key]['id'] = '<input type="checkbox" name="checkedInput" value="'.$val['asin'].'--'.$val['marketplace_id'].'">';
			$data[$key]['seller'] = array_get($sellers,$val['sap_seller_id']);
			$data[$key]['asin'] = ($type=='asin')?'<a href="/mrp/edit?asin='.$val['asin'].'&marketplace_id='.$val['marketplace_id'].'">'.$val['asin'].'</a>':$val['asin'];
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = ($type=='sku')?'<a href="/mrp/edit?keyword='.$val['sku'].'&marketplace_id='.$val['marketplace_id'].'">'.$val['sku'].'</a>':$val['sku'];
			$data[$key]['min_purchase'] = $val['min_purchase_quantity'];//最小起订量
			
			$data[$key]['total_sellable'] = intval($val['afn_sellable']+$val['afn_reserved']+$val['mfn_sellable']+$val['sz_sellable']);//可售库存数量
			$data[$key]['week_daily_sales'] = round($val['daily_sales']*7,2);//加权周销量
			$data[$key]['22_week_plan_total'] =0;
			$date_0w = date('Y-m-d',strtotime($date.'+0 weeks sunday'));//选定日期的本周末的日期
			$date_1wdate = date('Y-m-d',strtotime('+1 weeks sunday'));//当前时间的下周末的日期
			$date_update = date('Y-m-d',strtotime('+4 weeks sunday'));//可修改数据的日期，从这之后的数据都可以修改(以当前时间为主)
			$data[$key]['status_name'] = isset($asin_plans[$date_0w]) && $asin_plans[$date_0w]['status']==1 ? '已提交'  : '未提交';//本周数据的状态
			for($i=0;$i<=22;$i++){//$i=0时为本周的数据，展示本周和未来22周的预测数据
				$date_w = date('Y-m-d',strtotime($date.' +'.$i.' weeks sunday'));//第n周周天的日期
				//处理显示的预测销售数量,销售填写的预测数据只有销售本人才可以更改
				$quantity = array_get($asin_plans,$date_w.'.quantity',0);
				$data[$key][$i.'_week_plan'] = $quantity;
				$ok = isset($asin_plans[$date_w]) && $asin_plans[$date_w]['status']==1 ? 1 : 0;//确认状态，0表示未确认，1表示已确认
				if($updateRole==1 && $val['sap_seller_id']==$sap_seller_id &&  $type!='sku'){
					//此处为可编辑的显示内容
					if(($ok==0 && $date_w >= $date_1wdate) || ($ok==1 && $date_w>$date_update)){//未确认状态可以更新预测数据，如果已确认状态可以更新4周后的预测数据
						$data[$key][$i.'_week_plan'] = '<a class="week_plan editable" title="'.$val['asin'].' '.array_get($siteCode,$val['marketplace_id']).' weeks '.$i.' Plan" href="javascript:;" id="'.$val['asin'].'--'.$val['marketplace_id'].'--'.$val['sku'].'--'.$date_w.'" data-pk="'.$val['asin'].'--'.$val['marketplace_id'].'--'.$val['sku'].'--'.$date_w.'" data-type="text" data-placement="'.$data_placement.'">'.$quantity.'</a>';
					}
				}
				if($ok==0){//未确认的数据，加个背景色特殊显示
					$data[$key][$i.'_week_plan'] = '<div class="bg-danger">'.$data[$key][$i.'_week_plan'].'</div>';
				}
				if($i>0){//本周的数据不累加到总数里面
					$data[$key]['22_week_plan_total'] += $quantity;
				}
            }
		}
		
		return compact('data', 'recordsTotal', 'recordsFiltered');
    }

	//展示预测数据的详细数据
    public function edit(Request $request)
    {
		if(!Auth::user()->can(['sales-forecast-show'])) die('Permission denied -- sales-forecast-show');
		$keyword = $request->get('keyword');
		$asin=$sku='';
		$asin = $request->get('asin');
		$marketplace_id = $request->get('marketplace_id');
		$seller_permissions = $this->getUserSellerPermissions();

		$where='1=1';
		foreach($seller_permissions as $key=>$val){
			if($key=='bg' && $val) $where .=" and sap_seller_bg='$val'";
			if($key=='bu' && $val) $where .=" and sap_seller_bu='$val'";
			if($key=='sap_seller_id' && $val) $where .=" and sap_seller_id='$val'";
		}

		if(!$asin){
			$keyword_info = DB::connection('amazon')->table('sap_asin_match_sku')->where('marketplace_id',$marketplace_id)->whereRaw($where)
			->where(function ($query) use ($keyword) {
				$query->where('sku', $keyword)
				->orwhere('asin', $keyword);
            })->first();
			if(!empty($keyword_info)){
				$asin = $keyword_info->asin;
				$sku = $keyword_info->sku;
			}
		}else{
			$sku = DB::connection('amazon')->table('sap_asin_match_sku')->where('asin',$asin)->where('marketplace_id',$marketplace_id)->whereRaw($where)->value('sku');
			$keyword=$asin;
		}
		if(!$asin || !$sku){
			$request->session()->flash('error_message','No Data Match This Keywords');
            return redirect()->back()->withInput();
		}
		$type = $request->get('type')??'asin';
		//没选日期的话，默认查询最近90的数据
		$date=90;
		$date_to = $request->get('date_to')??date('Y-m-d',strtotime('+'.$date.'days'));
		$date_from = $tmp_date_from = $request->get('date_from')??date('Y-m-d');
		if($date_to<$date_from) $date_to = $date_from;
		//开始时间为date_from的周一日期，结束时间为date_to的周天日期(查询数据的开始时间和结束时间)
		$date_start = date("Y-m-d", strtotime('monday this week', strtotime($date_from)));
		$date_end = date("Y-m-d", strtotime('sunday this week', strtotime($date_to)));

		$asins = DB::connection('amazon')->select($this->getSql(" and a.sku = '$sku' and a.marketplace_id='$marketplace_id'",$date_start,$date_end,'',false));
		//sum_estimated_afn为fba在途数据
		$current_stock=0;//当前库存数量(FBA在库数量)
		foreach($asins as $v){
			if($type=='asin' && $v->asin==$asin){
				$current_stock=intval($v->afn_sellable+$v->afn_reserved);
			}
			if($type=='sku'){
				$current_stock+=intval($v->afn_sellable+$v->afn_reserved);
			}
		}

		$sales_plan=[];
		$asin_symmetrys = DB::connection('amazon')->table('symmetry_asins')->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('date','>=',$date_start)->where('date','<=',$date_end)->selectRaw("YEARWEEK(date,3) as wdate,sum(quantity) as quantity")->groupBy(['wdate'])->pluck('quantity','wdate');//预测销售数量

		$asin_historys = DailyStatistic::selectRaw("YEARWEEK(date,3) as wdate,sum(quantity_shipped) as sold,sum(if(date_format(date,'%w')=1,(afn_sellable+afn_reserved),0)) as stock")->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('date','>=',$date_start)->where('date','<=',$date_end)->groupBy(['wdate'])->get()->keyBy('wdate')->toArray();//该asin实际销量和库存

		$asin_plans = AsinSalesPlan::selectRaw("YEARWEEK(week_date,3) as wdate,sum(quantity_last) as quantity_last,sum(estimated_afn) as estimated_afn,any_value(remark) as remark")->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('week_date','>=',$date_start)->where('week_date','<=',$date_end)->groupBy(['wdate'])->get()->keyBy('wdate')->toArray();//销售填的预测数据

		$actual_shipment_datas = ShipmentRequest::selectRaw('sum(quantity) as quantity,YEARWEEK(received_date,3) as date')->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('received_date','>=',$date_start)->where('received_date','<=',$date_end)->where('shipment_completed',1)->whereNotNull('shipment_id')->where('status','<>',4)->groupBy(['date'])->pluck('quantity','date');//实际FBA上架(与FBA在途的区别在于状态为shipment_completed=1装运完成)

		$adjustreceived_shipment_datas = ShipmentRequest::selectRaw('YEARWEEK(adjustreceived_date,3) as date,max(adjustreceived_date) as adjustreceived_date')->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('received_date','>=',$date_start)->where('received_date','<=',$date_end)->where('shipment_completed',0)->whereNotNull('shipment_id')->where('status','<>',4)->groupBy(['date'])->pluck('adjustreceived_date','date');//预计到达时间

		$tmp_date_from = date('oW',strtotime($date_from));
		$tmp_date_to = date('oW',strtotime($date_to));//例如202042
		$oW=0;
		$updatePer = Auth::user()->can(['sales-forecast-update']) ? 1 : 0;

		while($tmp_date_from <= $tmp_date_to){
			$oW++;
			$plan_last = intval(array_get($asin_plans,$tmp_date_from.'.quantity_last',0));
			$sold = intval(array_get($asin_historys,$tmp_date_from.'.sold',0));
			$week_date = getWeekDate($tmp_date_from);//获取本周的周一跟周日的日期
			$remark = array_get($asin_plans,$tmp_date_from.'.remark','');
			if($type=='asin' && $updatePer){
				$remark = '<a class="remark editable" title="'.$asin.' '.$tmp_date_from.' Remark" href="javascript:;" id="'.$tmp_date_from.'--remark" data-pk="'.$tmp_date_from.'--remark" data-type="text"> '.$remark.' </a>';
			}

			$sales_plan[$tmp_date_from] = [
				'symmetry'=>intval(array_get($asin_symmetrys,$tmp_date_from,0)),
				'plan_last'=>$plan_last,
				'sold'=> $sold,
				'stock'=>intval(array_get($asin_historys,$tmp_date_from.'.stock',0)),
				'remark'=>$remark,
				'actual_afn'=>intval(array_get($actual_shipment_datas,$tmp_date_from,0)),//实际FBA上架
				'adjustreceived_date'=>array_get($adjustreceived_shipment_datas,$tmp_date_from,'-'),//预计到达时间
				'finishing_rate'=>$plan_last>0 ? (round($sold*100/$plan_last,2).'%') : '-',
				'week_date' => $week_date[0].'~'.$week_date[1],
				'estimated_afn' => array_get($asin_plans,$tmp_date_from.'.estimated_afn',0),
			];
			$tmp_date_from = date('oW',strtotime($date_from.' +'.$oW.' week'));
		}
		$cur_date=date('oW', time());//此时的周数202042

		return view('mrp/edit',['asin'=>$asin,'marketplace_id'=>$marketplace_id,'date_from'=>$date_from,'date_to'=>$date_to,'type'=>$type,'sku'=>$sku,'sales_plan'=>$sales_plan,'cur_date'=>$cur_date,'current_stock'=>$current_stock,'keyword'=>$keyword]);
    }

    //在详情页中修改备注的时候
    public function update(Request $request)
    {
		if(!Auth::user()->can(['sales-forecast-update'])) die('Permission denied -- sales-forecast-update');
		$asin = $request->get('asin');
		$marketplace_id = $request->get('marketplace_id');
		$sku = $request->get('sku');
		$key = explode('--',$request->get('name'));
		$date = array_get($key,0);
		$field = array_get($key,1);
		$value = $request->get('value');
		$data = AsinSalesPlan::updateOrCreate(
			[
				'asin' => $asin,
				'marketplace_id' => $marketplace_id,
				'week_date'=>getWeekDate($date)[1],
			],
			[
				$field=>$value,
				'sku'=>$sku,
				'updated_at'=>date('Y-m-d H:i:s')
			]
		);
		$return[$request->get('name')] = $value;
		echo json_encode($return);
    }
	
	//在22周销售预测功能的列表中更新预测数据时
	public function weekupdate(Request $request)
    {
		$key = explode('--',$request->get('name'));
		$asin = array_get($key,0);
		$marketplace_id = array_get($key,1);
		$sku = array_get($key,2);
		$week_date = array_get($key,3);
		$week_value = $request->get('value');
		$data = AsinSalesPlan::updateOrCreate(
			[
				'asin' => $asin,
				'marketplace_id' => $marketplace_id,
				'week_date'=>$week_date ,
			],
			[
				'quantity_last'=>$week_value,
				'sku'=>$sku,
				'updated_at'=>date('Y-m-d H:i:s')

			]
		);

		$data->save();
		$return[$request->get('name')] = $week_value;
		echo json_encode($return);
    }


    
    public function export(Request $request)
	{
		$search = $this->getSearchData(explode('&',$_SERVER['QUERY_STRING']));
		
		$date = array_get($search,'date')??date('Y-m-d');
		$date_from = date('Y-m-d',strtotime($date.' next monday'));
		$date_to = date('Y-m-d',strtotime($date.' +22 weeks sunday'));
		$searchField = array('bg'=>'a.bg','bu'=>'a.bu','site'=>'a.marketplace_id','sku'=>'a.sku','sku_level'=>'a.status','sku_status'=>'a.sku_status','sap_seller_id'=>'a.sap_seller_id');
		
		$where = $this->getSearchWhereSql($search,$searchField);
		
		if(array_get($search,'keyword')){
			$where .=" and (a.asin='".array_get($search,'keyword')."' or a.sku='".array_get($search,'keyword')."')";
		}
		$seller_permissions = $this->getUserSellerPermissions();
		foreach($seller_permissions as $key=>$val){
			if($key=='bg' && $val) $where .=" and a.bg='$val'";
			if($key=='bu' && $val) $where .=" and a.bu='$val'";
			if($key=='sap_seller_id' && $val) $where .=" and a.sap_seller_id='$val'";
		}
		$sql = $this->getSql($where,$date_from,$date_to,'',false);

		$datas = DB::connection('amazon')->select($sql);
		$datas = json_decode(json_encode($datas),true);
		$data = [];
		
		$headArray[] = '销售员';
        $headArray[] = 'Asin';
        $headArray[] = '站点';
		$headArray[] = 'Sku';
        $headArray[] = '最小起订量';
        $headArray[] = '周加权销量';
		$headArray[] = '总可售库存';
        $headArray[] = '销售计划合计';
		for($i=0;$i<=22;$i++){//本周的数据也要显示
        	$headArray[] = date('W',strtotime($date.' +'.$i.' weeks monday')-86400*7).'周 '.date('Y-m-d',strtotime($date.' +'.$i.' weeks monday')-86400*7);
        }
        $data[] = $headArray;
		
		$siteCode = array_flip(getSiteCode());
		$sellers = getUsers('sap_seller');
		foreach ($datas as $key => $val) {
			$key++;
			$asin_plans = AsinSalesPlan::SelectRaw('sum(quantity_last) as quantity,week_date')->where('asin',$val['asin'])->where('marketplace_id',$val['marketplace_id'])->where('week_date','>=',$date)->where('week_date','<=',$date_to)->groupBy(['week_date'])->get()->keyBy('week_date')->toArray();
			$plan_total[$key] =0;
			//$min_purchase_quantity = intval(SapPurchaseRecord::where('sku',$val['sku'])->where('sap_factory_code','<>','')->whereNotIn('supplier',['CN01','WH01','HK03'])->orderBy('created_date','desc')->value('min_purchase_quantity'));
			$data[$key]['seller'] = array_get($sellers,$val['sap_seller_id']);
			$data[$key]['asin'] = $val['asin'];
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = $val['sku'];
			$data[$key]['min_purchase'] = $val['min_purchase_quantity'];//最小起订量
			$data[$key]['week_daily_sales'] = round($val['daily_sales']*7);//加权周销量
			$data[$key]['total_sellable'] = intval($val['afn_sellable']+$val['afn_reserved']+$val['mfn_sellable']+$val['sz_sellable']);//可售库存数量
			$data[$key]['22_week_plan_total'] = &$plan_total[$key];
			for($i=0;$i<=22;$i++){//$i=0时为本周数据
				$data[$key][$i.'_week_plan'] = array_get($asin_plans,date('Y-m-d',strtotime($date.' +'.$i.' weeks sunday')).'.quantity',0);
				$plan_total[$key]+=intval($data[$key][$i.'_week_plan']);
            }
		}
		if($data){
            $spreadsheet = new Spreadsheet();

            $spreadsheet->getActiveSheet()
                ->fromArray(
                    $data,  // The data to set
                    NULL,        // Array values with this value will not be set
                    'A1'         // Top left coordinate of the worksheet range where
                //    we want to set these values (default is A1)
                );
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
            header('Content-Disposition: attachment;filename="Export_Mrp_Sales.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }
	
	public function asinExport(Request $request)
	{
		$search = $this->getSearchData(explode('&',$_SERVER['QUERY_STRING']));
		
		$date = (intval(array_get($search,'date'))>0)?intval(array_get($search,'date')):90;
		$searchField = array('bg'=>'a.bg','bu'=>'a.bu','site'=>'a.marketplace_id','sku'=>'a.sku','sku_level'=>'a.status','sku_status'=>'a.skus_status','sku_level'=>'a.status','sap_seller_id'=>'a.sap_seller_id');
		
		
		$where = $this->getSearchWhereSql($search,$searchField);

		if(array_get($search,'keyword')){
			$where .=" and (a.asin='".array_get($search,'keyword')."' or a.sku='".array_get($search,'keyword')."')";
		}
		$seller_permissions = $this->getUserSellerPermissions();
		foreach($seller_permissions as $key=>$val){
			if($key=='bg' && $val) $where .=" and a.bg='$val'";
			if($key=='bu' && $val) $where .=" and a.bu='$val'";
			if($key=='sap_seller_id' && $val) $where .=" and a.sap_seller_id='$val'";
		}
		$date_to = date('Y-m-d',strtotime('+'.$date.'days'));
		$date_from = date('Y-m-d');
		$sql = $this->getSql($where,$date_from,$date_to);
		$datas = DB::connection('amazon')->select($sql);
		$datas = json_decode(json_encode($datas),true);
		$data = [];
        $headArray[] = 'Asin';
        $headArray[] = 'Site';
		$headArray[] = 'Sku';
		$headArray[] = 'Status';
		$headArray[] = 'Seller';
		$headArray[] = 'D/Sales';
        $headArray[] = 'TotalPlan';
		
		$headArray[] = 'FBAStock';
		$headArray[] = 'FBAKeep';
		$headArray[] = 'FBATran';
        $headArray[] = 'FBMStock';
		$headArray[] = 'TotalKeep';
        $headArray[] = 'SZ';
		$headArray[] = 'InMake';
		$headArray[] = 'OutStock';
		$headArray[] = 'OutStockDate';
		$headArray[] = 'OverStock';
        $headArray[] = 'OverStockDate';
		$headArray[] = 'StockScore';
        $headArray[] = 'Dist';
        $data[] = $headArray;
		
		$siteCode = array_flip(getSiteCode());
		$sellers = getUsers('sap_seller');
		foreach ($datas as $key => $val) {
			$key++;
			$data[$key]['asin'] = $val['asin'];
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = $val['sku'];
			$data[$key]['status'] = $val['status'];
			$data[$key]['seller'] = array_get($sellers,$val['sap_seller_id']);
			$data[$key]['daily_sales'] = round($val['daily_sales']);
			$data[$key]['quantity'] = intval($val['quantity']);
			$data[$key]['fba_stock'] = $val['afn_sellable']+$val['afn_reserved'];
			$data[$key]['fba_stock_keep'] = ($val['afn_out_stock_date'])?date('Y-m-d',strtotime($val['afn_out_stock_date'])-86400):'';
			$data[$key]['fba_transfer'] = intval($val['sum_estimated_afn']);
			$data[$key]['fbm_stock'] = intval($val['mfn_sellable']);
			$data[$key]['stock_keep'] = ($val['out_stock_date'])?date('Y-m-d',strtotime($val['out_stock_date'])-86400):'';
			$data[$key]['sz'] = intval($val['sz_sellable']);
			$data[$key]['in_make'] = intval($val['sum_estimated_purchase']);
			$data[$key]['out_stock'] = intval($val['out_stock_count']);
			$data[$key]['out_stock_date'] = $val['out_stock_date'];
			$data[$key]['unsalable'] = intval($val['over_stock_count']);
			$data[$key]['unsalable_date'] = $val['over_stock_date'];
			$data[$key]['stock_score'] = intval($val['out_stock_count'])+intval($val['over_stock_count'])*3+intval($val['unsafe_count'])*4;
			$data[$key]['expected_distribution'] = (intval($val['afn_sellable']+$val['afn_reserved']+$val['mfn_sellable']+$val['sum_estimated_afn']-$val['sum_quantity_miss'])<0?abs(intval($val['afn_sellable']+$val['afn_reserved']+$val['mfn_sellable']+$val['sum_estimated_afn']-$val['sum_quantity_miss'])):0);
		}
		if($data){
            $spreadsheet = new Spreadsheet();

            $spreadsheet->getActiveSheet()
                ->fromArray(
                    $data,  // The data to set
                    NULL,        // Array values with this value will not be set
                    'A1'         // Top left coordinate of the worksheet range where
                //    we want to set these values (default is A1)
                );
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
            header('Content-Disposition: attachment;filename="Export_Mrp.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }

	public function getSql($where,$date_from,$date_to,$orderby='daily_sales desc',$cal_stock=true)
	{
		if($orderby){
			$orderby = " order by {$orderby} ";
		}else{
			$orderby = " order by daily_sales desc";
		}
		//列表页的sql
		$add_join =" left join (select sku,any_value(min_purchase_quantity) as min_purchase_quantity,any_value(created_date) as created_date from sap_purchase_records where sap_factory_code<>'' and supplier not in ('CN01','WH01','HK03') group by sku order by created_date desc) as c on a.sku=c.sku";
		$add_field = ",min_purchase_quantity ";
		if($cal_stock){//详情页的sql
			$add_where = [];
			foreach(getMarketplaceCode() as $k=>$v){
				foreach($v['fba_factory_warehouse'] as $k1=>$v1){
					$add_where[] ="(sap_factory_code = '".$v1['sap_factory_code']."' and sap_warehouse_code = '".$v1['sap_warehouse_code']."')";
				}
			}
			$add_join =" left join (select a1.asin,a1.marketplace_id,sum(quantity_last) as quantity,
sum(estimated_afn) as sum_estimated_afn,sum(estimated_purchase) as sum_estimated_purchase,
sum(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss<0,1,0)) as out_stock_count,
min(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss<0,a1.week_date,NULL)) as out_stock_date,
min(IF(afn_sellable+afn_reserved-quantity_miss<0,a1.week_date,NULL)) as afn_out_stock_date,
sum(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss>0 and a1.week_date>DATE_SUB(curdate(),INTERVAL -120 DAY),1,0)) as over_stock_count,
min(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss>0 and a1.week_date>DATE_SUB(curdate(),INTERVAL -120 DAY),a1.week_date,NULL)) as over_stock_date,
max(IF(a1.week_date='".$date_to."',quantity_miss,0)) as sum_quantity_miss,
sum(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss-sku_safe_quantity<0,1,0)) as unsafe_count
from asin_sales_plans as a1 left join asins as b1 on a1.asin=b1.asin and a1.marketplace_id=b1.marketplaceid
left join (select sku,marketplace_id,any_value(safe_quantity) as sku_safe_quantity  from sap_sku_sites where (".implode(" or ",$add_where).") group by sku,marketplace_id) as e on a1.sku=e.sku and a1.marketplace_id =e.marketplace_id where a1.week_date>='".$date_from."' and a1.week_date<='".$date_to."' group by asin,marketplace_id) as c
on a.asin=c.asin and a.marketplace_id=c.marketplace_id ";
			$add_field = ",quantity,sum_estimated_afn,sum_estimated_purchase,out_stock_count,out_stock_date,over_stock_count,over_stock_date,sum_quantity_miss,unsafe_count,afn_out_stock_date ";
		}
		$sql = "
        SELECT SQL_CALC_FOUND_ROWS
        	a.*,(sales_4_weeks/28*0.5+sales_2_weeks/14*0.3+sales_1_weeks/7*0.2) as daily_sales,buybox_sellerid,
afn_sellable,afn_reserved,mfn_sellable,sz_sellable ".$add_field." from (select asin,marketplace_id,any_value(sku) as sku,any_value(status) as status,
any_value(sku_status) as sku_status,any_value(sap_seller_id) as sap_seller_id, 
any_value(sap_seller_bg) as bg,any_value(sap_seller_bu) as bu from sap_asin_match_sku where sku_status<6 group by asin,marketplace_id) as a
left join asins as b on a.asin=b.asin and a.marketplace_id=b.marketplaceid
left join (select sku,sum(quantity) as sz_sellable from sap_sku_sites where left(sap_factory_code,2)='HK' group by sku) as d on a.sku=d.sku
".$add_join."
			where (sku_status>0 or (afn_sellable+afn_reserved+mfn_sellable+sz_sellable)>0) {$where} 
			{$orderby} ";
		return $sql;
	}

	public function getReturnData($data) {

		$siteCode = array_flip(getSiteCode());
		$sellers = getUsers('sap_seller');
		foreach ($data as $key => $val) {
			$data[$key]['asin'] = $val['asin'];
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = $val['sku'];
			$data[$key]['status'] = $val['status'];
			$data[$key]['seller'] = array_get($sellers,$val['sap_seller_id']);
			$data[$key]['daily_sales'] = round($val['daily_sales']);
			$data[$key]['quantity'] = intval($val['quantity']);
			$data[$key]['fba_stock'] = $val['afn_sellable']+$val['afn_reserved'];
			$data[$key]['fba_stock_keep'] = ($val['afn_out_stock_date'])?date('Y-m-d',strtotime($val['afn_out_stock_date'])-86400):'';
			$data[$key]['fba_transfer'] = intval($val['sum_estimated_afn']);
			$data[$key]['fbm_stock'] = intval($val['mfn_sellable']);
			$data[$key]['stock_keep'] = ($val['out_stock_date'])?date('Y-m-d',strtotime($val['out_stock_date'])-86400):'';
			$data[$key]['sz'] = intval($val['sz_sellable']);
			$data[$key]['in_make'] = intval($val['sum_estimated_purchase']);
			$data[$key]['out_stock'] = intval($val['out_stock_count']);
			$data[$key]['out_stock_date'] = $val['out_stock_date'];
			$data[$key]['unsalable'] = intval($val['over_stock_count']);
			$data[$key]['unsalable_date'] = $val['over_stock_date'];
			$data[$key]['stock_score'] = intval($val['out_stock_count'])+intval($val['over_stock_count'])*3+intval($val['unsafe_count'])*4;
			$data[$key]['expected_distribution'] = (intval($val['afn_sellable']+$val['afn_reserved']+$val['mfn_sellable']+$val['sum_estimated_afn']-$val['sum_quantity_miss'])<0?abs(intval($val['afn_sellable']+$val['afn_reserved']+$val['mfn_sellable']+$val['sum_estimated_afn']-$val['sum_quantity_miss'])):0);
			$data[$key]['action'] = '-';
		}
		return $data;
	}
	
	public function updateStatus(Request $request){
		$asinlist=$request->get('asinlist');
		$date=$request->get('date');
		$date_from = date('Y-m-d',strtotime($date.' +1 weeks sunday'));//下周末
		$date_to = date('Y-m-d',strtotime($date.' +22 weeks sunday'));//之后第22周的周末
		$asin_array = explode(',',$asinlist);
		foreach($asin_array as $val){
			$asin_marketplaceid = explode('--',$val);
			$add_where[] ="(asin = '".$asin_marketplaceid[0]."' and marketplace_id = '".$asin_marketplaceid[1]."')";
		}
		$result = AsinSalesPlan::where('week_date','>=',$date_from)->where('week_date','<=',$date_to)->whereRaw("(".implode(" or ",$add_where).")")->update(['status'=>1]);
		echo json_encode(['Ack'=>$result]);
	}
	
	public function import( Request $request )
	{	
		if($request->isMethod('POST')){
            $file = $request->file('importFile');  
  			if($file){
				if($file->isValid()){  
					$originalName = $file->getClientOriginalName();  
					$ext = $file->getClientOriginalExtension();  
					$type = $file->getClientMimeType();  
					$realPath = $file->getRealPath();  
					$newname = date('Y-m-d-H-i-S').'-'.uniqid().'.'.$ext;  
					$newpath = '/uploads/MrpUpload/'.date('Ymd').'/';
					$inputFileName = public_path().$newpath.$newname;
					$bool = $file->move(public_path().$newpath,$newname);
					if($bool){
						$time = date('Y-m-d H:i:s');
						$xls_keys = ['E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
						$current_date = date('Y-m-d');//当前时间戳
						// $current_date = '2020-05-01';//测试时间
						foreach($importData as $key => $data){
							$updateData=[];
							if($key>1 && array_get($data,'B') && array_get($data,'C')){
								$asin =trim(array_get($data,'B'));
								$marketplace_id =array_get(getSiteCode(),trim(array_get($data,'C')),trim(array_get($data,'C')));
								$sku = DB::connection('amazon')->table('sap_asin_match_sku')->where('asin',$asin)->where('marketplace_id',$marketplace_id)->value('sku');//检测此站点此asin是否有sku
								if(!$sku) continue;
								foreach($xls_keys as $k=>$v){
									$week_date = date('Y-m-d',strtotime($current_date."+".($k+1)." weeks Sunday"));//第n周的周天的日期
									$week_value = array_get($data,$v,0);//表格中此列的值,这周预测的值
									$updateData[] = array(
										'asin' => $asin,
										'marketplace_id' => $marketplace_id,
										'sku' => $sku,
										'week_date' => $week_date,
										'quantity_last' => $week_value,
										'updated_at' => $time
									);
								}
								if($updateData) AsinSalesPlan::insertOnDuplicateWithDeadlockCatching(array_values($updateData), ['week_date','quantity_last','sku','updated_at']);
								AsinSalesPlan::calPlans($asin,$marketplace_id,$sku,date('Y-m-d',strtotime("+1 Sunday")),date('Y-m-d',strtotime("+22 Sunday")));
							}
						}
						$request->session()->flash('success_message','Import Success!');
					}else{
						$request->session()->flash('error_message','UploadFailed');
					}          
				} 
			}else{
				$request->session()->flash('error_message','Please Select Upload File');
			} 
        } 
		return redirect('mrp/list');
	
	}

	/*
	 * 下载导入excel表格的模板
	 */
	public function download(Request $request)
	{
		// if(!Auth::user()->can(['edm-customers-add'])) die('Permission denied -- edm-customers-add');
		$filepath = 'Mrp_import_template.xlsx';
		$file=fopen($filepath,"r");
		header("Content-type:text/html;charset=utf-8");
		header("Content-Type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".filesize($filepath));
		header("Content-Disposition: attachment; filename=".$filepath);
		echo fread($file,filesize($filepath));
		fclose($file);
	}



}