<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Accounts;
use App\User;
use App\Group;
use App\AsinSalesPlan;
use App\DailyStatistic;
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
		if ($req->isMethod('GET')) {
			return view('mrp/list', ['bgs'=>$this->getBgs(),'bus'=>$this->getBus()]);
		}
		$date_from = date('Y-m-d',strtotime('+1 weeks monday'));
		$date_to = date('Y-m-d',strtotime('+22 weeks sunday'));
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
		$orderby = $this->dtOrderBy($req);
		
		$sql = $this->getSql($where,$date_from,$date_to,$orderby);
		
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
			$asin_plans = AsinSalesPlan::SelectRaw('sum(quantity_last) as quantity,week_date')->where('asin',$val['asin'])->where('marketplace_id',$val['marketplace_id'])->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['week_date'])->get()->keyBy('week_date')->toArray();
			$data[$key]['seller'] = array_get($sellers,$val['sap_seller_id']);
			$data[$key]['asin'] = '<a href="/mrp/edit?asin='.$val['asin'].'&marketplace_id='.$val['marketplace_id'].'">'.$val['asin'].'</a>';
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = $val['sku'];
			$data[$key]['min_purchase'] = 0;
			$data[$key]['week_daily_sales'] = round($val['daily_sales']*7,2);
			$data[$key]['22_week_plan_total'] = intval($val['quantity']);
			for($i=1;$i<=22;$i++){
				$data[$key][$i.'_week_plan'] = array_get($asin_plans,date('Y-m-d',strtotime('+'.$i.' weeks sunday')).'.quantity',0);
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
		$date = 90;
		$date_to = $request->get('date_to')??date('Y-m-d',strtotime('+'.$date.'days'));
		$date_from = $tmp_date_from = $request->get('date_from')??date('Y-m-d');
		if($date_to<$date_from) $date_to = $date_from;
		$asins = DB::connection('amazon')->select($this->getSql(" and a.sku = '$sku' and a.marketplace_id='$marketplace_id'",$date_from,$date_to));
		$sap_factory_code = array_get(MpToFc(),$marketplace_id,$marketplace_id);
		$sku_info = DB::connection('amazon')->table('sap_sku_sites')->where('sku',$sku)->where('marketplace_id',$marketplace_id)->where('sap_factory_code',$sap_factory_code)->first();
		$sales_plan=[];
		if($show=='week'){
			$asin_symmetrys = DB::connection('amazon')->table('symmetry_asins')->where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->selectRaw('YEARWEEK(date,3) as wdate,sum(quantity) as quantity')->groupBy(['wdate'])->pluck('quantity','wdate');
			$asin_historys = DailyStatistic::selectRaw('YEARWEEK(date,3) as wdate,sum(quantity_shipped) as sold,sum(afn_sellable+afn_reserved) as stock')->where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['wdate'])->get()->keyBy('wdate')->toArray();
			
			$asin_plans = AsinSalesPlan::selectRaw('YEARWEEK(date,3) as wdate,sum(quantity_last) as quantity_last,sum(quantity_first) as quantity_first,any_value(remark) as remark')->where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['wdate'])->get()->keyBy('wdate')->toArray();
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
				];
				$tmp_date_from = date('oW',strtotime($date_from.' +'.$oW.' week'));;
			}
			$cur_date=date('oW', time());
		}elseif($show=='month'){
			$asin_symmetrys = DB::connection('amazon')->table('symmetry_asins')->where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->selectRaw("DATE_FORMAT(date,'%Y-%m') as wdate,sum(quantity) as quantity")->groupBy(['wdate'])->pluck('quantity','wdate');
			$asin_historys = DailyStatistic::selectRaw("DATE_FORMAT(date,'%Y-%m') as wdate,sum(quantity_shipped) as sold,sum(afn_sellable+afn_reserved) as stock")->where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['wdate'])->get()->keyBy('wdate')->toArray();
			
			$asin_plans = AsinSalesPlan::selectRaw("DATE_FORMAT(date,'%Y-%m') as wdate,sum(quantity_last) as quantity_last,sum(quantity_first) as quantity_first,any_value(remark) as remark")->where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['wdate'])->get()->keyBy('wdate')->toArray();
			
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
				];
				$tmp_date_from = date('Y-m',strtotime($tmp_date_from.'-01 +1 month'));
			}
			$cur_date=date('Y-m');
		}else{
			$asin_symmetrys = DB::connection('amazon')->table('symmetry_asins')->where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->pluck('quantity','date');
			$asin_historys = DailyStatistic::selectRaw('date,sum(quantity_shipped) as sold,sum(afn_sellable+afn_reserved) as stock')->where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['date'])->get()->keyBy('date')->toArray();
			
			$asin_plans = AsinSalesPlan::where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->keyBy('date')->toArray();
			
			while($tmp_date_from<=$date_to){
				$sales_plan[$tmp_date_from] = [
					'symmetry'=>intval(array_get($asin_symmetrys,$tmp_date_from,0)),
					'plan_first'=>intval(array_get($asin_plans,$tmp_date_from.'.quantity_first',0)),
					'plan_last'=>intval(array_get($asin_plans,$tmp_date_from.'.quantity_last',0)),
					'sold'=>intval(array_get($asin_historys,$tmp_date_from.'.sold',0)),
					'stock'=>intval(array_get($asin_historys,$tmp_date_from.'.stock',0)),
					'remark'=>array_get($asin_plans,$tmp_date_from.'.remark'),
				];
				$tmp_date_from = date('Y-m-d',strtotime($tmp_date_from)+86400);
			}
			$cur_date=date('Y-m-d');
		}
		return view('mrp/edit',['asin'=>$asin,'marketplace_id'=>$marketplace_id,'date_from'=>$date_from,'date_to'=>$date_to,'show'=>$show,'asins'=>$asins,'sku_info'=>$sku_info,'sales_plan'=>$sales_plan,'cur_date'=>$cur_date,'keyword'=>$keyword]);
    }

    public function update(Request $request)
    {
		$asin = $request->get('asin');
		$marketplace_id = $request->get('marketplace_id');
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
		}
		$return[$request->get('name')] = $value;
		echo json_encode($return);
    }

    
    public function export(Request $request)
	{
		$search = $this->getSearchData(explode('&',$_SERVER['QUERY_STRING']));
		$date_from = date('Y-m-d',strtotime('+1 weeks monday'));
		$date_to = date('Y-m-d',strtotime('+22 weeks sunday'));
		$searchField = array('bg'=>'a.bg','bu'=>'a.bu','site'=>'a.marketplace_id','sku'=>'a.sku','sku_level'=>'a.status','sku_status'=>'a.sku_status','sku_level'=>'a.status','sap_seller_id'=>'a.sap_seller_id');
		
		$where = $this->getSearchWhereSql($search,$searchField);
		
		if(array_get($search,'keyword')){
			$where .=" and (a.asin='".array_get($search,'keyword')."' or a.sku='".array_get($search,'keyword')."')";
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
        $headArray[] = 'TotalPlan';
		for($i=1;$i<=22;$i++){
        	$headArray[] = 'Week '.$i;
        }
        $data[] = $headArray;
		
		$siteCode = array_flip(getSiteCode());
		$sellers = getUsers('sap_seller');
		foreach ($datas as $key => $val) {
			$key++;
			$asin_plans = AsinSalesPlan::SelectRaw('sum(quantity_last) as quantity,week_date')->where('asin',$val['asin'])->where('marketplace_id',$val['marketplace_id'])->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(['week_date'])->get()->keyBy('week_date')->toArray();
			$data[$key]['seller'] = array_get($sellers,$val['sap_seller_id']);
			$data[$key]['asin'] = $val['asin'];
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = $val['sku'];
			$data[$key]['min_purchase'] = 0;
			$data[$key]['week_daily_sales'] = round($val['daily_sales']*7,2);
			$data[$key]['22_week_plan_total'] = intval($val['quantity']);
			for($i=1;$i<=22;$i++){
				$data[$key][$i.'_week_plan'] = array_get($asin_plans,date('Y-m-d',strtotime('+'.$i.' weeks sunday')).'.quantity',0);
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
			$data[$key]['fba_stock_keep'] = 0;
			$data[$key]['fba_transfer'] = 0;
			$data[$key]['fbm_stock'] = 0;
			$data[$key]['stock_keep'] = 0;
			$data[$key]['sz'] = 0;
			$data[$key]['in_make'] = 0;
			$data[$key]['out_stock'] = 0;
			$data[$key]['out_stock_date'] = 0;
			$data[$key]['unsalable'] = 0;
			$data[$key]['unsalable_date'] = 0;
			$data[$key]['stock_score'] = 0;
			$data[$key]['expected_distribution'] = 0;
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
		
		$sql = "
        SELECT SQL_CALC_FOUND_ROWS
        	a.*,(sales_4_weeks/28*0.5+sales_2_weeks/14*0.3+sales_1_weeks/7*0.2) as daily_sales,buybox_sellerid,
afn_sellable,afn_reserved,quantity from (select asin,marketplace_id,any_value(sku) as sku,any_value(status) as status,
any_value(sku_status) as sku_status,any_value(sap_seller_id) as sap_seller_id, 
any_value(sap_seller_bg) as bg,any_value(sap_seller_bu) as bu from sap_asin_match_sku group by asin,marketplace_id) as a
left join asins as b on a.asin=b.asin and a.marketplace_id=b.marketplaceid
left join (select asin,marketplace_id,sum(quantity_last) as quantity from asin_sales_plans where date>='$date_from' and date<='$date_to' group by asin,marketplace_id) as c
on a.asin=c.asin and a.marketplace_id=c.marketplace_id
			where 1 = 1 {$where} 
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
			$data[$key]['fba_stock_keep'] = 0;
			$data[$key]['fba_transfer'] = 0;
			$data[$key]['fbm_stock'] = 0;
			$data[$key]['stock_keep'] = 0;
			$data[$key]['sz'] = 0;
			$data[$key]['in_make'] = 0;
			$data[$key]['out_stock'] = 0;
			$data[$key]['out_stock_date'] = 0;
			$data[$key]['unsalable'] = 0;
			$data[$key]['unsalable_date'] = 0;
			$data[$key]['stock_score'] = 0;
			$data[$key]['expected_distribution'] = 0;
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
						$xls_keys = ['H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC'];
						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
						$week_per = $this->week_per;		
						$updateData=[];
						foreach($importData as $key => $data){
							if($key>1 && array_get($data,'B') && array_get($data,'C')){
								$asin =trim(array_get($data,'B'));
								$marketplace_id =array_get(getSiteCode(),trim(array_get($data,'C')),trim(array_get($data,'C')));
								$sku = DB::connection('amazon')->table('sap_asin_match_sku')->where('asin',$asin)->where('marketplace_id',$marketplace_id)->value('sku');
								if(!$sku) break;
								foreach($xls_keys as $k=>$v){
									$week_date = date('Y-m-d',strtotime("+".($k+1)." Sunday"));
									$week_value = array_get($data,$v,0);
									$max_value=0;
									for($k=0;$k<=6;$k++){
										$date = date("Y-m-d", strtotime($week_date)-86400*$k);
										
										$value = round($week_value*array_get($week_per,$k));			
										if($max_value+$value>$week_value) $value = $week_value-$max_value;
										if($max_value<=$week_value && $k==6) $value = $week_value-$max_value;
										$max_value+=$value;
										$updateData[$date]['asin']=$asin;
										$updateData[$date]['marketplace_id']=$marketplace_id;
										$updateData[$date]['date']=$date;
										$updateData[$date]['week_date']=$week_date;
										$updateData[$date]['quantity_last']=$value;
										$updateData[$date]['updated_at']=$time;
									}
								}
							}
						}
						if($updateData) AsinSalesPlan::insertOnDuplicateWithDeadlockCatching(array_values($updateData), ['week_date','quantity_last','updated_at']);
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