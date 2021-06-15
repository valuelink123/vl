<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RoiPerformanceController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */
	use \App\Traits\DataTables;
	use \App\Traits\Mysqli;
	public function __construct()
	{
		$this->middleware('auth');
		parent::__construct();
	}

	/**
	 * Show the application dashboard
	 */
	public function index()
	{
		if(!Auth::user()->can(['roi-performance-show'])) die('Permission denied -- roi-performance-show');
		$sites = getCrmCountry();
		$type = $this->getTypeConfig();
		$data['fromDate'] = date("Y-m-d",strtotime("-1 years"));//开始日期,默认最近一年
		$data['toDate'] = date('Y-m-d');//结束日期
		return view('roi/roi_performance',['data'=>$data,'sites'=>$sites,'type'=>$type]);
	}

	/*
	 * ajax展示订单列表
	 */
	public function List(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$sql = $this->getSql($search,0);

		if($req['length'] != '-1'){//等于-1时为查看全部的数据
			$limit = $this->dtLimit($req);
			$sql .= " LIMIT {$limit} ";
		}
		$datas = DB::connection('amazon')->select($sql);
		$data = json_decode(json_encode($datas),true);
		$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;

//		$inventory_field = array('inventory_start','inventory_end','inventory_average');
		$typeConfig = $this->getTypeConfig();
		foreach($data as $key=>$val) {
			$data[$key]['type'] = isset($typeConfig[$val['type']]) ? $typeConfig[$val['type']] : $val['type'];
			$roi_id = $val['roi_id'];
			$data[$key]['action'] = '<td><button class="btn btn-success btn-sm sbold green-meadow calculate" data-id="'.$roi_id.'">计算</button></td>';
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	//计算绩效结果
	public function calculate()
	{
		$result = array('status'=>1,'msg'=>'计算完成');
		$roi_id = isset($_POST['roi_id']) ? $_POST['roi_id'] : '';
		$roi_id_array = array_unique(explode(";",$roi_id));
		$_roiData = DB::connection('amazon')->table('roi')->whereIn('id',$roi_id_array)->get()->toArray();

		if($_roiData) {
			$currency_rates = getCurrencyRates();
			foreach ($_roiData as $roiData) {
				$update_data['income']['value_total'] = 0.00;
				$update_data['cost']['value_total'] = 0.00;
				$update_data['commission']['value_total'] = 0.00;
				$update_data['operate_fee']['value_total'] = 0.00;
				$update_data['storage_fee']['value_total'] = 0.00;
				$update_data['capital_occupy_cost']['value_total'] = 0.00;
				$update_data['economic_benefit']['value_total'] = 0.00;

				$update_data['inventory_start']['value_total'] = 0.00;
				$update_data['inventory_end']['value_total'] = 0.00;
				$update_data['inventory_average']['value_total'] = 0.00;
				$update_data['promo']['value_total'] = 0.00;//总推广费

				$roiData = (array)$roiData;
				$unit_strorage_fee = getUnitStorageFee()[$roiData['site']];
				$unit_peak_storage_fee = $unit_strorage_fee[1];//旺季仓储费
				$unit_low_storage_fee = $unit_strorage_fee[0];//淡季仓储费

				for ($i = 1; $i <= 12; $i++) {
					//当月销售金额
					$currency_rate = $currency_rates[$roiData['site']];//当前站点的汇率
					$sales_amount_month_array[$i] = round($roiData['price_fc_month_' . $i] * $currency_rate * $roiData['volume_month_' . $i]);

					//每个月的收入,收入=当月销售金额-当月异常费用
					$exception_amount_month_array[$i] = $roiData['exception_rate_month_' . $i] * $sales_amount_month_array[$i];//当月异常费用
					$update_data['income']['value_month_' . $i] = sprintf("%.2f", $sales_amount_month_array[$i] - $exception_amount_month_array[$i]);
					$update_data['income']['value_total'] += $update_data['income']['value_month_' . $i];

					//每个月的成本,当月预计销量*（不含税采购单价成本+（总物流费用+总关税）/全年预计销量)
					$update_data['cost']['value_month_' . $i] = '0.00';
					$total_sales_volume = $roiData['total_sales_volume'];//年销售量
					if ($total_sales_volume > 0) {
						$update_data['cost']['value_month_' . $i] = sprintf("%.2f", $roiData['volume_month_' . $i] * ($roiData['purchase_price'] + ($roiData['year_import_tax'] + $roiData['year_transport']) / $total_sales_volume));
					}
					$update_data['cost']['value_total'] += $update_data['cost']['value_month_' . $i];

					//每个月的佣金,当月销售金额*平台佣金比例-当月销售金额*当月异常率*平台佣金比例*0.8
					$update_data['commission']['value_month_' . $i] = sprintf("%.2f", $sales_amount_month_array[$i] * $roiData['commission_rate'] - $sales_amount_month_array[$i] * $roiData['exception_rate_month_' . $i] * $roiData['commission_rate'] * 0.8);
					$update_data['commission']['value_total'] += $update_data['commission']['value_month_' . $i];

					//每个月的操作费，当月预计销量*平台操作费*汇率
					$update_data['operate_fee']['value_month_' . $i] = sprintf("%.2f", $roiData['volume_month_' . $i] * $roiData['unit_operating_fee'] * $currency_rate);
					$update_data['operate_fee']['value_total'] += $update_data['operate_fee']['value_month_' . $i];

					//每个月的推广费
					$update_data['promo']['value_month_' . $i] = $roiData['promo_rate_month_' . $i] * $sales_amount_month_array[$i];
					$update_data['promo']['value_total'] += $update_data['promo']['value_month_' . $i];

					//每个月的期末库存数量,剔除当月后三个月预计销量的滚动合计
					if ($i < 10) {
						$update_data['inventory_end']['value_month_' . $i] = round($roiData['volume_month_' . ($i + 1)] + $roiData['volume_month_' . ($i + 2)] + $roiData['volume_month_' . ($i + 3)]);
					} elseif ($i == 10) {
						$update_data['inventory_end']['value_month_' . $i] = round($roiData['volume_month_11'] + $roiData['volume_month_12'] + $roiData['volume_month_1']);
					} elseif ($i == 11) {
						$update_data['inventory_end']['value_month_' . $i] = round($roiData['volume_month_12'] + $roiData['volume_month_1'] + $roiData['volume_month_2']);
					} elseif ($i == 12) {
						$update_data['inventory_end']['value_month_' . $i] = round($roiData['volume_month_1'] + $roiData['volume_month_2'] + $roiData['volume_month_3']);
					}

					//每个月的期初库存数量,第一个月是上线前三个月预计销量合计，从第二个月开始就是上月期末库存数量
					if ($i == 1) {
						$update_data['inventory_start']['value_month_' . $i] = round($roiData['volume_month_1'] + $roiData['volume_month_2'] + $roiData['volume_month_3']);
					} else {
						$update_data['inventory_start']['value_month_' . $i] = round($update_data['inventory_end']['value_month_' . ($i - 1)]);
					}

					//每个月的平均库存数量,（期初库存数量+期末库存数量）/2
					$update_data['inventory_average']['value_month_' . $i] = round(($update_data['inventory_start']['value_month_' . $i] + $update_data['inventory_end']['value_month_' . $i]) / 2);
					
					//每个月的仓储费,如果月份为1月、11月、12月，则等于当月平均库存数量*旺季仓储费*汇率*单PCS体积/1000000，否则就是当月平均库存数量*淡季仓储费*汇率*单PCS体积/1000000
					//$unit_peak_storage_fee旺季仓储费，$unit_low_storage_fee淡季仓储费
					$update_data['storage_fee']['value_month_' . $i] = '0.00';
					if($roiData['estimated_launch_time']){
						$month = date("m", strtotime("+".($i-1)." months", strtotime($roiData['estimated_launch_time'])));
						if (in_array($month,array(11,12,01))) {
							//旺季仓储费
							$update_data['storage_fee']['value_month_' . $i] = sprintf("%.2f", $update_data['inventory_average']['value_month_' . $i]*$unit_peak_storage_fee*$currency_rate*$roiData['volume_per_pcs']/1000000);
						}else{
							//淡季仓储费
							$update_data['storage_fee']['value_month_' . $i] = sprintf("%.2f", $update_data['inventory_average']['value_month_' . $i]*$unit_low_storage_fee*$currency_rate*$roiData['volume_per_pcs']/1000000);
						}
					}

					$update_data['storage_fee']['value_total'] += $update_data['storage_fee']['value_month_' . $i];

					//每个月的资金占用成本,当月平均库存数量*（不含税采购单价成本+（总物流费用+总关税）/全年预计销量）*0.18/12
					$update_data['capital_occupy_cost']['value_month_' . $i] = 0.00;
					if ($total_sales_volume > 0) {
						$update_data['capital_occupy_cost']['value_month_' . $i] = sprintf("%.2f", $update_data['inventory_average']['value_month_' . $i] * ($roiData['purchase_price'] + ($roiData['year_import_tax'] + $roiData['year_transport']) / $total_sales_volume) * 0.18 / 12);
					}
					$update_data['capital_occupy_cost']['value_total'] += $update_data['capital_occupy_cost']['value_month_' . $i];


					//每个月的经济效益,收入-成本-佣金-操作费-仓储费-推广费-资金占用成本
					$update_data['economic_benefit']['value_month_' . $i] = sprintf("%.2f", $update_data['cost']['value_month_' . $i] - $update_data['cost']['value_month_' . $i] - $update_data['commission']['value_month_' . $i] - $update_data['operate_fee']['value_month_' . $i] - $update_data['storage_fee']['value_month_' . $i] - $update_data['promo']['value_month_' . $i] - $update_data['capital_occupy_cost']['value_month_' . $i]);
					$update_data['economic_benefit']['value_total'] += $update_data['economic_benefit']['value_month_' . $i];

				}
				$this->insertRoiPerformance($update_data, $roiData['id']);
			}
		}else{
			$result = array('status'=>0,'msg'=>'roiid数据异常');
		}
		return $result;

	}

	//插入数据到roi_performance表中
	public function insertRoiPerformance($data,$id)
	{
		unset($data['inventory_start']);
		unset($data['inventory_end']);
		unset($data['inventory_average']);
		$_data = DB::connection('amazon')->table('roi_performance')->where('roi_id',$id)->get()->toArray();
		if($_data){
			$performanceData = array();
			foreach($data as $key=>$val){
				$performanceData[$id.'_'.$key] = array(
					'value_month_1' => $val['value_month_1'],
					'value_month_2' => $val['value_month_2'],
					'value_month_3' => $val['value_month_3'],
					'value_month_4' => $val['value_month_4'],
					'value_month_5' => $val['value_month_5'],
					'value_month_6' => $val['value_month_6'],
					'value_month_7' => $val['value_month_7'],
					'value_month_8' => $val['value_month_8'],
					'value_month_9' => $val['value_month_9'],
					'value_month_10' => $val['value_month_10'],
					'value_month_11' => $val['value_month_11'],
					'value_month_12' => $val['value_month_12'],
					'value_total' => $val['value_total'],
					'updated_at' => date('Y-m-d H:i:s'),
				);
				DB::connection('amazon')->table('roi_performance')->where('roi_id',$id)->where('type',$key)->update($performanceData[$id.'_'.$key]);
			}

		}else{
			$performanceData = array();
			foreach($data as $key=>$val){
				$performanceData[$id.'_'.$key] = array(
					'roi_id' => $id,
					'type' => $key,
					'value_month_1' => $val['value_month_1'],
					'value_month_2' => $val['value_month_2'],
					'value_month_3' => $val['value_month_3'],
					'value_month_4' => $val['value_month_4'],
					'value_month_5' => $val['value_month_5'],
					'value_month_6' => $val['value_month_6'],
					'value_month_7' => $val['value_month_7'],
					'value_month_8' => $val['value_month_8'],
					'value_month_9' => $val['value_month_9'],
					'value_month_10' => $val['value_month_10'],
					'value_month_11' => $val['value_month_11'],
					'value_month_12' => $val['value_month_12'],
					'value_total' => $val['value_total'],
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				);
			}
			DB::connection('amazon')->table('roi_performance')->insert(array_values($performanceData));

		}
	}

	/*
	 * 导出功能
	 */
	public function export()
	{
		if(!Auth::user()->can(['roi-performance-export'])) die('Permission denied -- roi-performance-export');
		$sql = $this->getSql($_GET,1);

		$data = DB::connection('amazon')->select($sql);
		$data = json_decode(json_encode($data),true);

		$arrayData = array();
		$headArray[] = '上线月份';
		$headArray[] = 'SKU';
		$headArray[] = '描述';
		$headArray[] = '站点';
		for($i=1; $i<=12;$i++){
			$headArray[] = $i.'月销量';
		}
		$headArray[] = '合计销量';
		for($i=1; $i<=12;$i++){
			$headArray[] = $i.'月收入';
		}
		$headArray[] = '合计收入';

		for($i=1; $i<=12;$i++){
			$headArray[] = $i.'月成本';
		}
		$headArray[] = '合计成本';

		for($i=1; $i<=12;$i++){
			$headArray[] = $i.'月佣金';
		}
		$headArray[] = '合计佣金';

		for($i=1; $i<=12;$i++){
			$headArray[] = $i.'月拣配费';
		}
		$headArray[] = '合计拣配费';

		for($i=1; $i<=12;$i++){
			$headArray[] = $i.'月推广费';
		}
		$headArray[] = '合计推广费';

		for($i=1; $i<=12;$i++){
			$headArray[] = $i.'月仓储费';
		}
		$headArray[] = '合计仓储费';

		for($i=1; $i<=12;$i++){
			$headArray[] = $i.'月资金占用成本';
		}
		$headArray[] = '合计资金占用成本';

		for($i=1; $i<=12;$i++){
			$headArray[] = $i.'月经济效益';
		}
		$headArray[] = '合计经济效益';

		$arrayData[] = $headArray;


		foreach ($data as $key=>$val){
			$newData[$val['roi_id']]['estimated_launch_time'] = $val['estimated_launch_time'];//上线月份
			$newData[$val['roi_id']]['sku'] = $val['sku'];//SKU
			$newData[$val['roi_id']]['product_name'] = $val['product_name'];//描述
			$newData[$val['roi_id']]['site'] = $val['site'];//站点

			for($i=1; $i<=12;$i++) {
				$newData[$val['roi_id']][$val['type'] . '_value_month_' . $i] = $val['value_month_'.$i];
				$newData[$val['roi_id']]['volume_month_' . $i] = $val['volume_month_'.$i];//每个月的销量
			}
			$newData[$val['roi_id']][$val['type'] . '_value_total'] = $val['value_total'];
			$newData[$val['roi_id']]['total_sales_volume'] = $val['total_sales_volume'];//年销量
		}

		foreach ($newData as $key=>$val){
			$arrayData[] = array(
				$val['estimated_launch_time'],
				$val['sku'],
				$val['product_name'],
				$val['site'],

				//销量
				$val['volume_month_1'], $val['volume_month_2'], $val['volume_month_3'], $val['volume_month_4'], $val['volume_month_5'], $val['volume_month_6'], $val['volume_month_7'], $val['volume_month_8'], $val['volume_month_9'], $val['volume_month_10'], $val['volume_month_11'], $val['volume_month_12'], $val['total_sales_volume'],

				//收入
				$val['income_value_month_1'], $val['income_value_month_2'], $val['income_value_month_3'], $val['income_value_month_4'], $val['income_value_month_5'], $val['income_value_month_6'], $val['income_value_month_7'], $val['income_value_month_8'], $val['income_value_month_9'], $val['income_value_month_10'], $val['income_value_month_11'], $val['income_value_month_12'], $val['income_value_total'],

				//成本
				$val['cost_value_month_1'],$val['cost_value_month_2'],$val['cost_value_month_3'],$val['cost_value_month_4'],$val['cost_value_month_5'],$val['cost_value_month_6'],$val['cost_value_month_7'],$val['cost_value_month_8'],$val['cost_value_month_9'],$val['cost_value_month_10'],$val['cost_value_month_11'],$val['cost_value_month_12'],$val['cost_value_total'],

				//佣金
				$val['commission_value_month_1'],$val['commission_value_month_2'],$val['commission_value_month_3'],$val['commission_value_month_4'],$val['commission_value_month_5'],$val['commission_value_month_6'],$val['commission_value_month_7'],$val['commission_value_month_8'],$val['commission_value_month_9'],$val['commission_value_month_10'],$val['commission_value_month_11'],$val['commission_value_month_12'],$val['commission_value_total'],

				//拣配费
				$val['operate_fee_value_month_1'],$val['operate_fee_value_month_2'],$val['operate_fee_value_month_3'],$val['operate_fee_value_month_4'],$val['operate_fee_value_month_5'],$val['operate_fee_value_month_6'],$val['operate_fee_value_month_7'],$val['operate_fee_value_month_8'],$val['operate_fee_value_month_9'],$val['operate_fee_value_month_10'],$val['operate_fee_value_month_11'],$val['operate_fee_value_month_12'],$val['operate_fee_value_total'],

				//推广费
				$val['promo_value_month_1'],$val['promo_value_month_2'],$val['promo_value_month_3'],$val['promo_value_month_4'],$val['promo_value_month_5'],$val['promo_value_month_6'],$val['promo_value_month_7'],$val['promo_value_month_8'],$val['promo_value_month_9'],$val['promo_value_month_10'],$val['promo_value_month_11'],$val['promo_value_month_12'],$val['promo_value_total'],

				//仓储费
				$val['storage_fee_value_month_1'],$val['storage_fee_value_month_2'],$val['storage_fee_value_month_3'],$val['storage_fee_value_month_4'],$val['storage_fee_value_month_5'],$val['storage_fee_value_month_6'],$val['storage_fee_value_month_7'],$val['storage_fee_value_month_8'],$val['storage_fee_value_month_9'],$val['storage_fee_value_month_10'],$val['storage_fee_value_month_11'],$val['storage_fee_value_month_12'],$val['storage_fee_value_total'],

				//资金占用成本
				$val['capital_occupy_cost_value_month_1'],$val['capital_occupy_cost_value_month_2'],$val['capital_occupy_cost_value_month_3'],$val['capital_occupy_cost_value_month_4'],$val['capital_occupy_cost_value_month_5'],$val['capital_occupy_cost_value_month_6'],$val['capital_occupy_cost_value_month_7'],$val['capital_occupy_cost_value_month_8'],$val['capital_occupy_cost_value_month_9'],$val['capital_occupy_cost_value_month_10'],$val['capital_occupy_cost_value_month_11'],$val['capital_occupy_cost_value_month_12'],$val['storage_fee_value_total'],

				//经济效益
				$val['economic_benefit_value_month_1'],$val['economic_benefit_value_month_2'],$val['economic_benefit_value_month_3'],$val['economic_benefit_value_month_4'],$val['economic_benefit_value_month_5'],$val['economic_benefit_value_month_6'],$val['economic_benefit_value_month_7'],$val['economic_benefit_value_month_8'],$val['economic_benefit_value_month_9'],$val['economic_benefit_value_month_10'],$val['economic_benefit_value_month_11'],$val['economic_benefit_value_month_12'],$val['economic_benefit_value_total'],

			);
		}

		if($arrayData){
			$spreadsheet = new Spreadsheet();
			$spreadsheet->getActiveSheet()
				->fromArray(
					$arrayData,  // The data to set
					NULL,        // Array values with this value will not be set
					'A1'         // Top left coordinate of the worksheet range where
				//    we want to set these values (default is A1)
				);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
			header('Content-Disposition: attachment;filename="Export_ROI_Performance.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}

	//获得搜索条件并且返回对应的sql语句
	public function getSql($search,$flag)
	{
		//搜索条件如下：from_date,to_date,account,status,amazon_order_id,asin,tracking_no,carry_code,settlement_id,settlement_date
		$where = " where roi_performance.created_at >= '".$search['from_date']." 00:00:00' and roi_performance.created_at <= '".$search['to_date']." 23:59:59'";
		$where .= "and sku is not null ";
		if(isset($search['sku']) && $search['sku']){
			$where.= " and sku = '".trim($search['sku'])."'";
		}
		if(isset($search['site']) && $search['site']){
			$where.= " and site = '".trim($search['site'])."'";
		}
		//roi_id,type
		if(isset($search['roi_id']) && $search['roi_id']){
			$where.= " and roi_id = '".trim($search['roi_id'])."'";
		}
		if($flag==0){
			if(isset($search['type']) && $search['type']){
				$where.= " and type = '".trim($search['type'])."'";
			}
		}

		$sql = "select SQL_CALC_FOUND_ROWS roi_performance.*,roi.*
				from roi_performance
				left join roi on roi_performance.roi_id = roi.id
				 {$where}
				order by roi_id desc,type desc";
		return $sql;
	}


	public function getTypeConfig()
	{
		$typeConfig = array(
			'income'=>'收入',
			'cost'=>'成本',
			'commission'=>'佣金',
			'operate_fee'=>'操作费',
			'storage_fee'=>'仓储费',
			'capital_occupy_cost'=>'资金占用成本',
			'economic_benefit'=>'经济效益',
			'promo'=>'推广费',
		);
		return $typeConfig;
	}

}