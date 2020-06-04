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
		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		
		$date = (intval(array_get($search,'date'))>0)?intval(array_get($search,'date')):90;
		if ($req->isMethod('GET')) {
			return view('mrp/index', ['date' => $date,'bgs'=>$this->getBgs(),'bus'=>$this->getBus()]);
		}
		$searchField = array('bg'=>'a.bg','bu'=>'a.bu','site'=>'a.marketplace_id','sku'=>'a.sku','sku_level'=>'a.status','sku_status'=>'a.skus_status','sku_level'=>'a.status','sap_seller_id'=>'a.sap_seller_id');
		
		
		$where = $this->getSearchWhereSql($search,$searchField);

		if(array_get($search,'keyword')){
			$where .=" and (a.asin='".array_get($search,'keyword')."' or a.sku='".array_get($search,'keyword')."')";
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
		$sql = $this->getSql($where,$date_from,$date_to,$orderby);
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
		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		
		$date = array_get($search,'date')??date('Y-m-d');
		if ($req->isMethod('GET')) {
			return view('mrp/list', ['date'=>$date,'bgs'=>$this->getBgs(),'bus'=>$this->getBus()]);
		}
		$date_from = date('Y-m-d',strtotime($date.' next monday'));
		$date_to = date('Y-m-d',strtotime($date.' +22 weeks sunday'));
		$searchField = array('bg'=>'a.bg','bu'=>'a.bu','site'=>'a.marketplace_id','sku'=>'a.sku','sku_level'=>'a.status','sku_status'=>'a.sku_status','sku_level'=>'a.status','sap_seller_id'=>'a.sap_seller_id');
		
		$where = $this->getSearchWhereSql($search,$searchField);

		$type = array_get($search,'type');
		if($type!='sku') $type='asin';
			
		if(array_get($search,'keyword')){
			$where .=" and (a.asin='".array_get($search,'keyword')."' or a.sku='".array_get($search,'keyword')."')";
		}
		$seller_permissions = $this->getUserSellerPermissions();
		foreach($seller_permissions as $key=>$val){
			if($key=='bg' && $val) $where .=" and a.bg='$val'";
			if($key=='bu' && $val) $where .=" and a.bu='$val'";
			if($key=='sap_seller_id' && $val) $where .=" and a.sap_seller_id='$val'";
		}
		$orderby = $this->dtOrderBy($req);
		
		$sql = $this->getSql($where,$date_from,$date_to,$orderby);
		
		
			
		if($type=='sku'){
			$sql="SELECT SQL_CALC_FOUND_ROWS sku,marketplace_id,any_value(sap_seller_id) as sap_seller_id,  count(asin) as asin, sum(daily_sales) as daily_sales,sum(quantity) as quantity,sum(afn_sellable) as afn_sellable,sum(afn_reserved) as afn_reserved,any_value(mfn_sellable) as mfn_sellable,any_value(sz_sellable) as sz_sellable from (".str_replace('SQL_CALC_FOUND_ROWS','',$sql).") as skus_table 
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
		foreach ($datas as $key => $val) {
			$min_purchase_quantity = intval(SapPurchaseRecord::where('sku',$val['sku'])->where('sap_factory_code','<>','')->whereNotIn('supplier',['CN01','WH01','HK03'])->orderBy('created_date','desc')->value('min_purchase_quantity'));
			$data_placement ='top';
			if($key<4) $data_placement='bottom';
			$asin_plans = AsinSalesPlan::SelectRaw('sum(quantity_last) as quantity,week_date')->where($type,$val[$type])->where('marketplace_id',$val['marketplace_id'])->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['week_date'])->get()->keyBy('week_date')->toArray();
			$data[$key]['seller'] = array_get($sellers,$val['sap_seller_id']);
			$data[$key]['asin'] = ($type=='asin')?'<a href="/mrp/edit?asin='.$val['asin'].'&marketplace_id='.$val['marketplace_id'].'">'.$val['asin'].'</a>':$val['asin'];
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = ($type=='sku')?'<a href="/mrp/edit?keyword='.$val['sku'].'&marketplace_id='.$val['marketplace_id'].'">'.$val['sku'].'</a>':$val['sku'];
			$data[$key]['min_purchase'] = $min_purchase_quantity;
			
			$data[$key]['total_sellable'] = intval($val['afn_sellable']+$val['afn_reserved']+$val['mfn_sellable']+$val['sz_sellable']);
			$data[$key]['week_daily_sales'] = round($val['daily_sales']*7,2);
			$data[$key]['22_week_plan_total'] = intval($val['quantity']);
			
			for($i=1;$i<=22;$i++){
				$data[$key][$i.'_week_plan'] = ($type!='sku' && date('Y-m-d',strtotime($date.' +'.$i.' weeks sunday'))>=date('Y-m-d',strtotime('+1 weeks sunday')))?('<a class="week_plan editable" title="'.$val['asin'].' '.array_get($siteCode,$val['marketplace_id']).' weeks '.$i.' Plan" href="javascript:;" id="'.$val['asin'].'--'.$val['marketplace_id'].'--'.$val['sku'].'--'.date('Y-m-d',strtotime($date.' +'.$i.' weeks sunday')).'" data-pk="'.$val['asin'].'--'.$val['marketplace_id'].'--'.$val['sku'].'--'.date('Y-m-d',strtotime($date.' +'.$i.' weeks sunday')).'" data-type="text" data-placement="'.$data_placement.'">'.array_get($asin_plans,date('Y-m-d',strtotime($date.' +'.$i.' weeks sunday')).'.quantity',0).'</a>'):array_get($asin_plans,date('Y-m-d',strtotime($date.' +'.$i.' weeks sunday')).'.quantity',0);
            }
		}
		
		return compact('data', 'recordsTotal', 'recordsFiltered');
    }


    public function edit(Request $request)
    {
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
		$show = $request->get('show')??'day';
		$type = $request->get('type')??'asin';
		$date=90;
		$date_to = $request->get('date_to')??date('Y-m-d',strtotime('+'.$date.'days'));
		$date_from = $tmp_date_from = $request->get('date_from')??date('Y-m-d');
		if($date_to<$date_from) $date_to = $date_from;
		$asins = DB::connection('amazon')->select($this->getSql(" and a.sku = '$sku' and a.marketplace_id='$marketplace_id'",$date_from,$date_to));
		$whereInAsins = [];
		$current_stock=0;
		foreach($asins as $v){
			$whereInAsins[]=$v->asin;
			if($type=='asin' && $v->asin==$asin){
				$current_stock=intval($v->afn_sellable+$v->afn_reserved);
			}
			if($type=='sku'){
				$current_stock+=intval($v->afn_sellable+$v->afn_reserved);
			} 
		}
		
		
		$add_where = [];
		foreach(getMarketplaceCode() as $k=>$v){
			foreach($v['fba_factory_warehouse'] as $k1=>$v1){
				$add_where[] ="(sap_factory_code = '".$v1['sap_factory_code']."' and sap_warehouse_code = '".$v1['sap_warehouse_code']."')";
			}
		}
		$sku_info = SapSkuSite::where('sku',$sku)->where('marketplace_id',$marketplace_id)->whereRaw("(".implode(' or ',$add_where).")")->first()->toArray();
		$sku_purchase_info = SapPurchaseRecord::where('sku',$sku)->where('sap_factory_code','<>','')->whereNotIn('supplier',['CN01','WH01','HK03'])->orderBy('created_date','desc')->first();
		$sku_info['min_purchase_quantity'] = empty($sku_purchase_info)?0:$sku_purchase_info->min_purchase_quantity;
		$sku_info['estimated_cycle'] = empty($sku_purchase_info)?0:$sku_purchase_info->estimated_cycle;
		$sku_info['international_transport_time'] = InternationalTransportTime::where('factory_code',array_get(getMarketplaceCode(),$marketplace_id.'.fba_factory_warehouse.0.sap_factory_code'))->where('is_default',1)->value('total_days');
		
		
		$sales_plan=[];
		if($show=='week'){
			$asin_symmetrys = DB::connection('amazon')->table('symmetry_asins')->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->selectRaw("YEARWEEK(date,3) as wdate,sum(quantity) as quantity")->groupBy(['wdate'])->pluck('quantity','wdate');
			$asin_historys = DailyStatistic::selectRaw("YEARWEEK(date,3) as wdate,sum(quantity_shipped) as sold,sum(if(date_format(date,'%w')=1,(afn_sellable+afn_reserved),0)) as stock")->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['wdate'])->get()->keyBy('wdate')->toArray();
			
			$asin_plans = AsinSalesPlan::selectRaw('YEARWEEK(date,3) as wdate,sum(quantity_last) as quantity_last,sum(quantity_first) as quantity_first,any_value(remark) as remark')->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['wdate'])->get()->keyBy('wdate')->toArray();
			
			$estimated_shipment_datas = ShipmentRequest::selectRaw('sum(quantity) as quantity,YEARWEEK(received_date,3) as date')->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('received_date','>=',$date_from)->where('received_date','<=',$date_to)->where('shipment_completed',0)->groupBy(['date'])->pluck('quantity','date');
			
			$actual_shipment_datas = ShipmentRequest::selectRaw('sum(quantity) as quantity,YEARWEEK(received_date,3) as date')->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('received_date','>=',$date_from)->where('received_date','<=',$date_to)->where('shipment_completed',1)->groupBy(['date'])->pluck('quantity','date');
		
			$estimated_purchase_datas = SapPurchase::selectRaw('sum(quantity) as quantity,YEARWEEK(estimated_delivery_date,3) as date')->where('sku',$sku)->where('estimated_delivery_date','>=',$date_from)->where('estimated_delivery_date','<=',$date_to)->whereNull('actual_delivery_date')->groupBy(['date'])->pluck('quantity','date');
			
			$actual_purchase_datas = SapPurchase::selectRaw('sum(quantity) as quantity,YEARWEEK(actual_delivery_date,3) as date')->where('sku',$sku)->where('actual_delivery_date','>=',$date_from)->where('actual_delivery_date','<=',$date_to)->whereNotNull('actual_delivery_date')->groupBy(['date'])->pluck('quantity','date');
		
			$tmp_date_from = date('oW',strtotime($date_from));
			$tmp_date_to = date('oW',strtotime($date_to));
			$oW=0;
			while($tmp_date_from<=$tmp_date_to){
				$oW++;
				$sales_plan[$tmp_date_from] = [
					'symmetry'=>intval(array_get($asin_symmetrys,$tmp_date_from,0)),
					'plan_first'=>intval(array_get($asin_plans,$tmp_date_from.'.quantity_first',0)),
					'plan_last'=>intval(array_get($asin_plans,$tmp_date_from.'.quantity_last',0)),
					'sold'=>intval(array_get($asin_historys,$tmp_date_from.'.sold',0)),
					'stock'=>intval(array_get($asin_historys,$tmp_date_from.'.stock',0)),
					'remark'=>array_get($asin_plans,$tmp_date_from.'.remark'),
					'estimated_purchase'=>intval(array_get($estimated_purchase_datas,$tmp_date_from,0)),
					'actual_purchase'=>intval(array_get($actual_purchase_datas,$tmp_date_from,0)),
					'estimated_afn'=>intval(array_get($estimated_shipment_datas,$tmp_date_from,0)),
					'actual_afn'=>intval(array_get($actual_shipment_datas,$tmp_date_from,0)),
				];
				$tmp_date_from = date('oW',strtotime($date_from.' +'.$oW.' week'));;
			}
			$cur_date=date('oW', time());
		}elseif($show=='month'){
			$asin_symmetrys = DB::connection('amazon')->table('symmetry_asins')->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->selectRaw("DATE_FORMAT(date,'%Y-%m') as wdate,sum(quantity) as quantity")->groupBy(['wdate'])->pluck('quantity','wdate');
			$asin_historys = DailyStatistic::selectRaw("DATE_FORMAT(date,'%Y-%m') as wdate,sum(quantity_shipped) as sold,sum(if(date=concat(DATE_FORMAT(date,'%Y-%m'),'-01'),(afn_sellable+afn_reserved),0)) as stock")->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['wdate'])->get()->keyBy('wdate')->toArray();
			
			$asin_plans = AsinSalesPlan::selectRaw("DATE_FORMAT(date,'%Y-%m') as wdate,sum(quantity_last) as quantity_last,sum(quantity_first) as quantity_first,any_value(remark) as remark")->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['wdate'])->get()->keyBy('wdate')->toArray();
			
			$estimated_shipment_datas = ShipmentRequest::selectRaw("sum(quantity) as quantity,DATE_FORMAT(received_date,'%Y-%m') as date")->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('received_date','>=',$date_from)->where('received_date','<=',$date_to)->where('shipment_completed',0)->groupBy(['date'])->pluck('quantity','date');
			
			$actual_shipment_datas = ShipmentRequest::selectRaw("sum(quantity) as quantity,DATE_FORMAT(received_date,'%Y-%m') as date")->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('received_date','>=',$date_from)->where('received_date','<=',$date_to)->where('shipment_completed',1)->groupBy(['date'])->pluck('quantity','date');
		
			$estimated_purchase_datas = SapPurchase::selectRaw("sum(quantity) as quantity,DATE_FORMAT(estimated_delivery_date,'%Y-%m') as date")->where('sku',$sku)->where('estimated_delivery_date','>=',$date_from)->where('estimated_delivery_date','<=',$date_to)->whereNull('actual_delivery_date')->groupBy(['date'])->pluck('quantity','date');
			
			$actual_purchase_datas = SapPurchase::selectRaw("sum(quantity) as quantity,DATE_FORMAT(actual_delivery_date,'%Y-%m') as date")->where('sku',$sku)->where('actual_delivery_date','>=',$date_from)->where('actual_delivery_date','<=',$date_to)->whereNotNull('actual_delivery_date')->groupBy(['date'])->pluck('quantity','date');
			
			$tmp_date_from = date('Y-m',strtotime($date_from));
			$tmp_date_to = date('Y-m',strtotime($date_to));
			while($tmp_date_from<=$tmp_date_to){
				$sales_plan[$tmp_date_from] = [
					'symmetry'=>intval(array_get($asin_symmetrys,$tmp_date_from,0)),
					'plan_first'=>intval(array_get($asin_plans,$tmp_date_from.'.quantity_first',0)),
					'plan_last'=>intval(array_get($asin_plans,$tmp_date_from.'.quantity_last',0)),
					'sold'=>intval(array_get($asin_historys,$tmp_date_from.'.sold',0)),
					'stock'=>intval(array_get($asin_historys,$tmp_date_from.'.stock',0)),
					'remark'=>array_get($asin_plans,$tmp_date_from.'.remark'),
					'estimated_purchase'=>intval(array_get($estimated_purchase_datas,$tmp_date_from,0)),
					'actual_purchase'=>intval(array_get($actual_purchase_datas,$tmp_date_from,0)),
					'estimated_afn'=>intval(array_get($estimated_shipment_datas,$tmp_date_from,0)),
					'actual_afn'=>intval(array_get($actual_shipment_datas,$tmp_date_from,0)),
				];
				$tmp_date_from = date('Y-m',strtotime($tmp_date_from.'-01 +1 month'));
			}
			$cur_date=date('Y-m');
		}else{
			$asin_symmetrys = DB::connection('amazon')->table('symmetry_asins')->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->pluck('quantity','date');
			$asin_historys = DailyStatistic::selectRaw('date,sum(quantity_shipped) as sold,sum(afn_sellable+afn_reserved) as stock')->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['date'])->get()->keyBy('date')->toArray();
			
			$asin_plans = AsinSalesPlan::where($type,${$type})->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->keyBy('date')->toArray();
			
			$estimated_shipment_datas = ShipmentRequest::selectRaw("sum(quantity) as quantity,received_date as date")->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('received_date','>=',$date_from)->where('received_date','<=',$date_to)->where('shipment_completed',0)->groupBy(['date'])->pluck('quantity','date');
			
			$actual_shipment_datas = ShipmentRequest::selectRaw("sum(quantity) as quantity,received_date as date")->where($type,${$type})->where('marketplace_id',$marketplace_id)->where('received_date','>=',$date_from)->where('received_date','<=',$date_to)->where('shipment_completed',1)->groupBy(['date'])->pluck('quantity','date');
		
			$estimated_purchase_datas = SapPurchase::selectRaw("sum(quantity) as quantity,estimated_delivery_date as date")->where('sku',$sku)->where('estimated_delivery_date','>=',$date_from)->where('estimated_delivery_date','<=',$date_to)->whereNull('actual_delivery_date')->groupBy(['date'])->pluck('quantity','date');
			
			$actual_purchase_datas = SapPurchase::selectRaw("sum(quantity) as quantity,actual_delivery_date as date")->where('sku',$sku)->where('actual_delivery_date','>=',$date_from)->where('actual_delivery_date','<=',$date_to)->whereNotNull('actual_delivery_date')->groupBy(['date'])->pluck('quantity','date');
			
			while($tmp_date_from<=$date_to){
				$sales_plan[$tmp_date_from] = [
					'symmetry'=>intval(array_get($asin_symmetrys,$tmp_date_from,0)),
					'plan_first'=>intval(array_get($asin_plans,$tmp_date_from.'.quantity_first',0)),
					'plan_last'=>intval(array_get($asin_plans,$tmp_date_from.'.quantity_last',0)),
					'sold'=>intval(array_get($asin_historys,$tmp_date_from.'.sold',0)),
					'stock'=>intval(array_get($asin_historys,$tmp_date_from.'.stock',0)),
					'remark'=>array_get($asin_plans,$tmp_date_from.'.remark'),
					'estimated_purchase'=>intval(array_get($estimated_purchase_datas,$tmp_date_from,0)),
					'actual_purchase'=>intval(array_get($actual_purchase_datas,$tmp_date_from,0)),
					'estimated_afn'=>intval(array_get($estimated_shipment_datas,$tmp_date_from,0)),
					'actual_afn'=>intval(array_get($actual_shipment_datas,$tmp_date_from,0)),
				];
				$tmp_date_from = date('Y-m-d',strtotime($tmp_date_from)+86400);
			}
			$cur_date=date('Y-m-d');
		}
		return view('mrp/edit',['asin'=>$asin,'marketplace_id'=>$marketplace_id,'date_from'=>$date_from,'date_to'=>$date_to,'show'=>$show,'type'=>$type,'asins'=>$asins,'sku'=>$sku,'sku_info'=>$sku_info,'sales_plan'=>$sales_plan,'cur_date'=>$cur_date,'current_stock'=>$current_stock,'keyword'=>$keyword]);
    }

    public function update(Request $request)
    {
		$asin = $request->get('asin');
		$marketplace_id = $request->get('marketplace_id');
		$sku = $request->get('sku');
		$date_to = $request->get('date_to');
		$date_from = $request->get('date_from');
		$key = explode('--',$request->get('name'));
		$date = array_get($key,0);
		$field = array_get($key,1);
		$value = $request->get('value');
		$data = AsinSalesPlan::updateOrCreate(
						[
							'asin' => $asin,
							'marketplace_id' => $marketplace_id,
							'date'=>$date
						],
						[
							$field=>$value,
							'sku'=>$sku,
							'week_date'=>date('Y-m-d',strtotime("$date Sunday")) ,
							'updated_at'=>date('Y-m-d H:i:s')
							
						]
					);
		if($field=='quantity_last'){
			if($data->quantity_first == 0){
				$data->quantity_first=$value;
				$return[$date.'--quantity_first'] = $value;
			}
			$data->save();
			
			$return[$asin] = AsinSalesPlan::selectRaw("sum(quantity_last) as quantity_last")->where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->value('quantity_last');
			AsinSalesPlan::calPlans($asin,$marketplace_id,$sku,date('Y-m-d'),date('Y-m-d',strtotime("+22 Sunday")));
		}
		$return[$request->get('name')] = $value;
		echo json_encode($return);
    }
	
	
	public function weekupdate(Request $request)
    {
		$key = explode('--',$request->get('name'));
		$asin = array_get($key,0);
		$marketplace_id = array_get($key,1);
		$sku = array_get($key,2);
		$week_date = array_get($key,3);
		$week_value = $request->get('value');
		$max_value=0;
		$week_per = $this->week_per;
		for($ki=0;$ki<=6;$ki++){
			$date = date("Y-m-d", strtotime($week_date)-86400*$ki);
			
			$value = round($week_value*array_get($week_per,$ki));			
			if($max_value+$value>$week_value) $value = $week_value-$max_value;
			if($max_value<=$week_value && $ki==6) $value = $week_value-$max_value;
			$max_value+=$value;
			$data = AsinSalesPlan::updateOrCreate(
				[
					'asin' => $asin,
					'marketplace_id' => $marketplace_id,
					'date'=>$date
				],
				[
					'quantity_last'=>$value,
					'sku'=>$sku,
					'week_date'=>$week_date ,
					'updated_at'=>date('Y-m-d H:i:s')
					
				]
			);
			if($data->quantity_first == 0){
				$data->quantity_first=$value;
			}
			$data->save();
		}
									
		
		$return[$request->get('name')] = $week_value;
		echo json_encode($return);
    }


    
    public function export(Request $request)
	{
		$search = $this->getSearchData(explode('&',$_SERVER['QUERY_STRING']));
		
		$date = array_get($search,'date')??date('Y-m-d');
		$date_from = date('Y-m-d',strtotime($date.' next monday'));
		$date_to = date('Y-m-d',strtotime($date.' +22 weeks sunday'));
		$searchField = array('bg'=>'a.bg','bu'=>'a.bu','site'=>'a.marketplace_id','sku'=>'a.sku','sku_level'=>'a.status','sku_status'=>'a.sku_status','sku_level'=>'a.status','sap_seller_id'=>'a.sap_seller_id');
		
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
		$sql = $this->getSql($where,$date_from,$date_to,'');

		$datas = DB::connection('amazon')->select($sql);
		$datas = json_decode(json_encode($datas),true);
		$data = [];
		
		$headArray[] = 'Seller';
        $headArray[] = 'Asin';
        $headArray[] = 'Site';
		$headArray[] = 'Sku';
        $headArray[] = 'Min Purchase';
        $headArray[] = 'W/Sales';
		$headArray[] = 'TotalSellable';
        $headArray[] = 'TotalPlan';
		for($i=1;$i<=22;$i++){
        	$headArray[] = date('Y-m-d',strtotime($date.' +'.$i.' weeks monday')-86400*7);
        }
        $data[] = $headArray;
		
		$siteCode = array_flip(getSiteCode());
		$sellers = getUsers('sap_seller');
		foreach ($datas as $key => $val) {
			$key++;
			$asin_plans = AsinSalesPlan::SelectRaw('sum(quantity_last) as quantity,week_date')->where('asin',$val['asin'])->where('marketplace_id',$val['marketplace_id'])->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['week_date'])->get()->keyBy('week_date')->toArray();
			
			$min_purchase_quantity = intval(SapPurchaseRecord::where('sku',$val['sku'])->where('sap_factory_code','<>','')->whereNotIn('supplier',['CN01','WH01','HK03'])->orderBy('created_date','desc')->value('min_purchase_quantity'));
			$data[$key]['seller'] = array_get($sellers,$val['sap_seller_id']);
			$data[$key]['asin'] = $val['asin'];
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = $val['sku'];
			$data[$key]['min_purchase'] = $min_purchase_quantity;
			$data[$key]['week_daily_sales'] = round($val['daily_sales']*7,2);
			$data[$key]['total_sellable'] = intval($val['afn_sellable']+$val['afn_reserved']+$val['mfn_sellable']+$val['sz_sellable']);
			$data[$key]['22_week_plan_total'] = intval($val['quantity']);
			for($i=1;$i<=22;$i++){
				$data[$key][$i.'_week_plan'] = array_get($asin_plans,date('Y-m-d',strtotime($date.' +'.$i.' weeks sunday')).'.quantity',0);
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
            header('Content-Disposition: attachment;filename="Export_Mrp.xlsx"');//告诉浏览器输出浏览器名称
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
			$data[$key]['daily_sales'] = round($val['daily_sales'],2);
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

	public function getSql($where,$date_from,$date_to,$orderby='daily_sales desc')
	{
		if($orderby){
			$orderby = " order by {$orderby} ";
		}else{
			$orderby = " order by daily_sales desc";
		}
		$add_where = [];
		foreach(getMarketplaceCode() as $k=>$v){
			foreach($v['fba_factory_warehouse'] as $k1=>$v1){
				$add_where[] ="(sap_factory_code = '".$v1['sap_factory_code']."' and sap_warehouse_code = '".$v1['sap_warehouse_code']."')";
			}
		}
		
		
		$sql = "
        SELECT SQL_CALC_FOUND_ROWS
        	a.*,(sales_4_weeks/28*0.5+sales_2_weeks/14*0.3+sales_1_weeks/7*0.2) as daily_sales,buybox_sellerid,
afn_sellable,afn_reserved,mfn_sellable,sz_sellable,quantity,sum_estimated_afn,sum_estimated_purchase,out_stock_count,out_stock_date,over_stock_count,over_stock_date,sum_quantity_miss,unsafe_count,afn_out_stock_date from (select asin,marketplace_id,any_value(sku) as sku,any_value(status) as status,
any_value(sku_status) as sku_status,any_value(sap_seller_id) as sap_seller_id, 
any_value(sap_seller_bg) as bg,any_value(sap_seller_bu) as bu from sap_asin_match_sku where sku_status<6 group by asin,marketplace_id) as a
left join asins as b on a.asin=b.asin and a.marketplace_id=b.marketplaceid
left join (select sku,sum(quantity) as sz_sellable from sap_sku_sites where left(sap_factory_code,2)='HK' group by sku) as d on a.sku=d.sku
left join (select a1.asin,a1.marketplace_id,sum(quantity_last) as quantity,
sum(estimated_afn) as sum_estimated_afn,sum(estimated_purchase) as sum_estimated_purchase,
sum(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss<0,1,0)) as out_stock_count,
min(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss<0,a1.date,NULL)) as out_stock_date,
min(IF(afn_sellable+afn_reserved-quantity_miss<0,a1.date,NULL)) as afn_out_stock_date,
sum(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss>0 and a1.date>DATE_SUB(curdate(),INTERVAL -120 DAY),1,0)) as over_stock_count,
min(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss>0 and a1.date>DATE_SUB(curdate(),INTERVAL -120 DAY),a1.date,NULL)) as over_stock_date,

max(IF(a1.date='".$date_to."',quantity_miss,0)) as sum_quantity_miss,
sum(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss-sku_safe_quantity<0,1,0)) as unsafe_count
from asin_sales_plans as a1 left join asins as b1 on a1.asin=b1.asin and a1.marketplace_id=b1.marketplaceid
left join (select sku,marketplace_id,any_value(safe_quantity) as sku_safe_quantity  from sap_sku_sites where (".implode(" or ",$add_where).") group by sku,marketplace_id) as e on a1.sku=e.sku and a1.marketplace_id =e.marketplace_id where a1.date>='".$date_from."' and a1.date<='".$date_to."' group by asin,marketplace_id) as c
on a.asin=c.asin and a.marketplace_id=c.marketplace_id
			where (sku_status>0 or (afn_sellable+afn_reserved+mfn_sellable+sz_sellable)>0) {$where} 
			{$orderby} ";
		return $sql;
	}


	public function getReturnData($data) {

		$siteCode = array_flip(getSiteCode());
		$sellers = getUsers('sap_seller');
		foreach ($data as $key => $val) {
			$data[$key]['asin'] = '<a href="/mrp/edit?asin='.$val['asin'].'&marketplace_id='.$val['marketplace_id'].'">'.$val['asin'].'</a>';
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = $val['sku'];
			$data[$key]['status'] = $val['status'];
			$data[$key]['seller'] = array_get($sellers,$val['sap_seller_id']);
			$data[$key]['daily_sales'] = round($val['daily_sales'],2);
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
			$data[$key]['action'] = '<a class="badge badge-success" href="/mrp/edit?asin='.$val['asin'].'&marketplace_id='.$val['marketplace_id'].'"><i class="fa fa-hand-o-up"></i></a>';
		}
		return $data;
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
						$xls_keys = ['I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD'];
						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
						$week_per = $this->week_per;		
						
						foreach($importData as $key => $data){
							$updateData=[];
							if($key>1 && array_get($data,'B') && array_get($data,'C')){
								$asin =trim(array_get($data,'B'));
								$marketplace_id =array_get(getSiteCode(),trim(array_get($data,'C')),trim(array_get($data,'C')));
								$sku = DB::connection('amazon')->table('sap_asin_match_sku')->where('asin',$asin)->where('marketplace_id',$marketplace_id)->value('sku');
								if(!$sku) continue;
								foreach($xls_keys as $k=>$v){
									$week_date = date('Y-m-d',strtotime("+".($k+1)." weeks Sunday"));
									$week_value = array_get($data,$v,0);
									$max_value=0;
									for($ki=0;$ki<=6;$ki++){
										$date = date("Y-m-d", strtotime($week_date)-86400*$ki);
										
										$value = round($week_value*array_get($week_per,$ki));			
										if($max_value+$value>$week_value) $value = $week_value-$max_value;
										if($max_value<=$week_value && $ki==6) $value = $week_value-$max_value;
										$max_value+=$value;
										$updateData[$date]['asin']=$asin;
										$updateData[$date]['marketplace_id']=$marketplace_id;
										$updateData[$date]['date']=$date;
										$updateData[$date]['sku']=$sku;
										$updateData[$date]['week_date']=$week_date;
										$updateData[$date]['quantity_last']=$value;
										$updateData[$date]['updated_at']=$time;
										
										$data = AsinSalesPlan::updateOrCreate(
											[
												'asin' => $asin,
												'marketplace_id' => $marketplace_id,
												'date'=>$date
											],
											[
												'quantity_last'=>$value,
												'sku'=>$sku,
												'week_date'=>$week_date ,
												'updated_at'=>$time
												
											]
										);
										if($data->quantity_first == 0){
											$data->quantity_first=$value;
										}
										$data->save();
									}
								}
								AsinSalesPlan::calPlans($asin,$marketplace_id,$sku,date('Y-m-d'),date('Y-m-d',strtotime("+22 Sunday")));
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



}