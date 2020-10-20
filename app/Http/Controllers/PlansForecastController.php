<?php

namespace App\Http\Controllers;

use App\AsinSalesPlan;
use App\DailyStatistic;
use App\InternationalTransportTime;
use App\SapPurchase;
use App\SapPurchaseRecord;
use App\SapSkuSite;
use App\ShipmentRequest;
use Illuminate\Http\Request;
use \App\Models\AsinPlansPlan;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;
use App\Models\AsinData;
class PlansForecastController extends Controller
{

	use \App\Traits\Mysqli;
	use \App\Traits\DataTables;
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
	//列表功能
	public function list(Request $req)
	{
		if(!Auth::user()->can(['plans-forecast-show'])) die('Permission denied -- plans-forecast-show');
		$updateRole = Auth::user()->can(['plans-forecast-update']) ? 1 : 0;//是否有更新数据的权限
		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$date = array_get($search,'date')??date('Y-m-d');
		if ($req->isMethod('GET')) {
			return view('forecast/plansList', ['date'=>$date,'bgs'=>$this->getBgs(),'bus'=>$this->getBus(),'weekDate'=>$this->get22WeekDate($date)]);
		}

		$date_from = date('Y-m-d',strtotime($date.' next monday'));
		$date_to = date('Y-m-d',strtotime($date.' +22 weeks sunday'));
		$searchField = array('site'=>'a.marketplace_id','sku'=>'a.sku','sku_level'=>'a.status','sku_level'=>'a.status','sap_seller_id'=>'a.sap_seller_id');

		$where = $this->getSearchWhereSql($search,$searchField);
		$type='sku';//之前$type有asin和sku维度，现销售只要asin维度,计划只要sku维度

		if(array_get($search,'keyword')){
			$where .=" and (a.asin='".array_get($search,'keyword')."' or a.sku='".array_get($search,'keyword')."')";
		}

		if(isset($search['sku_status'])){
			$where .=" and a.sku_status in (".array_get($search,'sku_status').")";
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
		$sap_seller_id = Auth::user()->sap_seller_id;
		foreach ($datas as $key => $val) {
			$data_placement ='top';
			if($key<4) $data_placement='bottom';
			$asin_plans = AsinPlansPlan::SelectRaw('sum(quantity_last) as quantity,week_date,any_value(status) as status')->where($type,$val[$type])->where('marketplace_id',$val['marketplace_id'])->where('week_date','>=',$date)->where('week_date','<=',$date_to)->groupBy(['week_date'])->get()->keyBy('week_date')->toArray();

			$data[$key]['id'] = '<input type="checkbox" name="checkedInput" value="'.$val['asin'].'--'.$val['marketplace_id'].'">';
			$data[$key]['asin'] = ($type=='asin')?'<a href="/plansforecast/edit?asin='.$val['asin'].'&marketplace_id='.$val['marketplace_id'].'">'.$val['asin'].'</a>':$val['asin'];
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = ($type=='sku')?'<a href="/mrp/edit?keyword='.$val['sku'].'&marketplace_id='.$val['marketplace_id'].'">'.$val['sku'].'</a>':$val['sku'];
			$data[$key]['min_purchase'] = $val['min_purchase_quantity'];

			$data[$key]['total_sellable'] = intval($val['afn_sellable']+$val['afn_reserved']+$val['mfn_sellable']+$val['sz_sellable']);
			$data[$key]['week_daily_sales'] = round($val['daily_sales']*7,2);
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
				// if($updateRole==1 && $type!='sku' && ($ok==0 && $date_w >= $date_1wdate) || ($ok==1 && $date_w>$date_update)){
				// 	//此处为可编辑的显示内容
				// 	//未确认状态可以更新预测数据，如果已确认状态可以更新4周后的预测数据
				// 	$data[$key][$i.'_week_plan'] = '<a class="week_plan editable" title="'.$val['asin'].' '.array_get($siteCode,$val['marketplace_id']).' weeks '.$i.' Plan" href="javascript:;" id="'.$val['asin'].'--'.$val['marketplace_id'].'--'.$val['sku'].'--'.$date_w.'" data-pk="'.$val['asin'].'--'.$val['marketplace_id'].'--'.$val['sku'].'--'.$date_w.'" data-type="text" data-placement="'.$data_placement.'">'.$quantity.'</a>';
				// }
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

	//展示某个asin的情况
	public function edit(Request $request)
	{
		$keyword = $request->get('keyword');
		$asin=$sku='';
		$asin = $request->get('asin');
		$marketplace_id = $request->get('marketplace_id');
		$where='1=1';
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

		$date=90;//没选日期默认最近90天
		$date_to = $request->get('date_to')??date('Y-m-d',strtotime('+'.$date.'days'));
		$date_from = $tmp_date_from = $request->get('date_from')??date('Y-m-d');
		if($date_to<$date_from) $date_to = $date_from;
		//开始时间为date_from的周一日期，结束时间为date_to的周天日期(查询数据的开始时间和结束时间)
		$date_start = date("Y-m-d", strtotime('monday this week', strtotime($date_from)));
		$date_end = date("Y-m-d", strtotime('sunday this week', strtotime($date_to)));
		$asins = DB::connection('amazon')->select($this->getSql(" and a.sku = '$sku' and a.marketplace_id='$marketplace_id'",$date_start,$date_end));

		$asin_plans = AsinPlansPlan::selectRaw("sum(quantity_last) as quantity_last,asin")->where('sku',$sku)->where('marketplace_id',$marketplace_id)->where('week_date','>=',$date_start)->where('week_date','<=',$date_end)->groupBy(['asin'])->get()->keyBy('asin')->toArray();//计划填的预测数据

		foreach($asins as $key=>$val){
			$asins[$key]->quantity = '0';
			if(isset($asin_plans[$val->asin])){
				$asins[$key]->quantity = $asin_plans[$val->asin]['quantity_last'];
			}
		}

		$add_where = [];
		foreach(getMarketplaceCode() as $k=>$v){
			foreach($v['fba_factory_warehouse'] as $k1=>$v1){
				$add_where[] ="(sap_factory_code = '".$v1['sap_factory_code']."' and sap_warehouse_code = '".$v1['sap_warehouse_code']."')";
			}
		}
		//$sku_info为第二个表格的sku信息
		$sku_info = SapSkuSite::where('sku',$sku)->where('marketplace_id',$marketplace_id)->whereRaw("(".implode(' or ',$add_where).")")->first()->toArray();
		$sku_purchase_info = SapPurchaseRecord::where('sku',$sku)->where('sap_factory_code','<>','')->whereNotIn('supplier',['CN01','WH01','HK03'])->orderBy('created_date','desc')->first();
		$sku_info['min_purchase_quantity'] = empty($sku_purchase_info)?0:$sku_purchase_info->min_purchase_quantity;
		$sku_info['estimated_cycle'] = empty($sku_purchase_info)?0:$sku_purchase_info->estimated_cycle;
		$sku_info['international_transport_time'] = InternationalTransportTime::where('factory_code',array_get(getMarketplaceCode(),$marketplace_id.'.fba_factory_warehouse.0.sap_factory_code'))->where('is_default',1)->value('total_days');

		return view('forecast/plansEdit',['asin'=>$asin,'marketplace_id'=>$marketplace_id,'date_from'=>$date_from,'date_to'=>$date_to,'asins'=>$asins,'sku'=>$sku,'sku_info'=>$sku_info,'keyword'=>$keyword]);
	}

	//在列表中更新某一个周数的计划数据
	public function weekupdate(Request $request)
	{
		$key = explode('--',$request->get('name'));
		$asin = array_get($key,0);
		$marketplace_id = array_get($key,1);
		$sku = array_get($key,2);
		$week_date = array_get($key,3);
		$week_value = $request->get('value');
		$data = AsinPlansPlan::updateOrCreate(
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


	//导出数据功能
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

		$sql = $this->getSql($where,$date_from,$date_to,'',false);

		$datas = DB::connection('amazon')->select($sql);
		$datas = json_decode(json_encode($datas),true);
		$data = [];

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

		foreach ($datas as $key => $val) {
			$key++;
			$asin_plans = AsinPlansPlan::SelectRaw('sum(quantity_last) as quantity,week_date')->where('asin',$val['asin'])->where('marketplace_id',$val['marketplace_id'])->where('week_date','>=',$date)->where('week_date','<=',$date_to)->groupBy(['week_date'])->get()->keyBy('week_date')->toArray();
			$plan_total[$key] =0;
			//$min_purchase_quantity = intval(SapPurchaseRecord::where('sku',$val['sku'])->where('sap_factory_code','<>','')->whereNotIn('supplier',['CN01','WH01','HK03'])->orderBy('created_date','desc')->value('min_purchase_quantity'));
			$data[$key]['asin'] = $val['asin'];
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = $val['sku'];
			$data[$key]['min_purchase'] = $val['min_purchase_quantity'];
			$data[$key]['week_daily_sales'] = round($val['daily_sales']*7);
			$data[$key]['total_sellable'] = intval($val['afn_sellable']+$val['afn_reserved']+$val['mfn_sellable']+$val['sz_sellable']);
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
			header('Content-Disposition: attachment;filename="Export_Mrp_Plans.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}

	//确认数据,确认数据的时候不更改查询日期的周末的状态，只更新查询日期的未来22周的状态
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
		$result = AsinPlansPlan::where('week_date','>=',$date_from)->where('week_date','<=',$date_to)->whereRaw("(".implode(" or ",$add_where).")")->update(['status'=>1]);
		echo json_encode(['Ack'=>$result]);
	}
	//导入功能
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

						foreach($importData as $key => $data){
							$updateData=[];
							if($key>1 && array_get($data,'B') && array_get($data,'C')){
								$asin =trim(array_get($data,'B'));
								$marketplace_id =array_get(getSiteCode(),trim(array_get($data,'C')),trim(array_get($data,'C')));
								$sku = DB::connection('amazon')->table('sap_asin_match_sku')->where('asin',$asin)->where('marketplace_id',$marketplace_id)->value('sku');//检测此站点此asin是否有sku
								if(!$sku) continue;
								foreach($xls_keys as $k=>$v){
									$week_date = date('Y-m-d',strtotime("+".($k+1)." weeks Sunday"));//第n周的周天的日期
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
								if($updateData) AsinPlansPlan::insertOnDuplicateWithDeadlockCatching(array_values($updateData), ['week_date','quantity_last','sku','updated_at']);
								AsinData::calPlans(2,$asin,$marketplace_id,$sku,date('Y-m-d',strtotime("+1 Sunday")),date('Y-m-d',strtotime("+22 Sunday")));
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
		return redirect('plansforecast/list');

	}

	public function getSql($where,$date_from,$date_to,$orderby='daily_sales desc',$cal_stock=true)
	{
		if($orderby){
			$orderby = " order by {$orderby} ";
		}else{
			$orderby = " order by daily_sales desc";
		}
		if($cal_stock){
			$add_where = [];
			foreach(getMarketplaceCode() as $k=>$v){
				foreach($v['fba_factory_warehouse'] as $k1=>$v1){
					$add_where[] ="(sap_factory_code = '".$v1['sap_factory_code']."' and sap_warehouse_code = '".$v1['sap_warehouse_code']."')";
				}
			}
			$add_join =" left join (select a1.asin,a1.marketplace_id,
sum(estimated_afn) as sum_estimated_afn,sum(estimated_purchase) as sum_estimated_purchase,
sum(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss_plan<0,1,0)) as out_stock_count,
min(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss_plan<0,a1.week_date,NULL)) as out_stock_date,
min(IF(afn_sellable+afn_reserved-quantity_miss_plan<0,a1.week_date,NULL)) as afn_out_stock_date,
sum(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss_plan>0 and a1.week_date>DATE_SUB(curdate(),INTERVAL -120 DAY),1,0)) as over_stock_count,
min(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss_plan>0 and a1.week_date>DATE_SUB(curdate(),INTERVAL -120 DAY),a1.week_date,NULL)) as over_stock_date,
max(IF(a1.week_date='".$date_to."',quantity_miss_plan,0)) as sum_quantity_miss,
sum(IF(afn_sellable+afn_reserved+mfn_sellable-quantity_miss_plan-sku_safe_quantity<0,1,0)) as unsafe_count
from asin_data as a1 left join asins as b1 on a1.asin=b1.asin and a1.marketplace_id=b1.marketplaceid
left join (select sku,marketplace_id,any_value(safe_quantity) as sku_safe_quantity  from sap_sku_sites where (".implode(" or ",$add_where).") group by sku,marketplace_id) as e on a1.sku=e.sku and a1.marketplace_id =e.marketplace_id where a1.week_date>='".$date_from."' and a1.week_date<='".$date_to."' group by asin,marketplace_id) as c
on a.asin=c.asin and a.marketplace_id=c.marketplace_id ";
			$add_field = ",sum_estimated_afn,sum_estimated_purchase,out_stock_count,out_stock_date,over_stock_count,over_stock_date,sum_quantity_miss,unsafe_count,afn_out_stock_date ";
		}else{
			$add_join =" left join (select sku,any_value(min_purchase_quantity) as min_purchase_quantity,any_value(created_date) as created_date from sap_purchase_records where sap_factory_code<>'' and supplier not in ('CN01','WH01','HK03') group by sku order by created_date desc) as c on a.sku=c.sku";
			$add_field = ",min_purchase_quantity ";
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



}