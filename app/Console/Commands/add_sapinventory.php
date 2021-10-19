<?php
/* Date: 2021.10.19
 * Author: wulanfnag
 * 每小时跑一次批处理文件，用于获取sap库存数据，并且存放在表里面，一条库存盘点记录就有两条库存数据，一条是类型为BEFORE，表示处理前的库存数据，一条类型是AFTER表示这条数据是处理后的数据，处理后的数据每一小时更新一次，只有当库存盘点的状态为已确认后才不需要再更新处理后的数据
 * 每小时跑一次此处理
 * */
namespace App\Console\Commands;

use App\AsinSalesPlan;
use Illuminate\Console\Command;
use DB;
use Log;
use App\Models\InventoryCycleCount;
use App\Models\InventoryCycleCountReason;
use App\Models\InventoryCycleCountSapInventory;

class AddSapInventory extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'add:sap_inventory';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	protected $date = '';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

	}

	public function __destruct()
	{

	}

	//添加库存预警数据
	function handle()
	{
		set_time_limit(0);
		$today = date('Y-m-d');
		Log::Info('Add sap_inventory Start...');
		//插入处理前数据
		DB::enableQueryLog();
		$_data = InventoryCycleCount::leftJoin('inventory_cycle_count_sapinventory', function ($join) {
			$join->on('inventory_cycle_count_sapinventory.inventory_cycle_count_id', '=', 'inventory_cycle_count.id')
				->where('type', '=', 'BEFORE');
		})->select('inventory_cycle_count.*')->where('inventory_cycle_count.date','<',$today)->where('inventory_cycle_count_sapinventory.inventory_cycle_count_id',null)->get()->toArray();
		log::info(response()->json(DB::getQueryLog()));

		foreach($_data as $key=>$val){
			$skus = array(array('MATNR'=>$val['sku']));
			$start_date = $end_date = date('Y-m-d',strtotime($val['date']));
			$sap_inventory_data = $this->getSkuInventoryBySapApi($skus,$start_date,$end_date);
			$_sapdata = $sap_inventory_data[$val['sku']][$val['factory'].'_'.$val['location']];
			$insertSapData = array(
				'inventory_cycle_count_id' => $val['id'],
				'date' => $val['date'],
				'sku' => $val['sku'],
				'factory' => $val['factory'],
				'location' => $val['location'],
				'type' => 'BEFORE',
				'BUKRS' => $_sapdata['BUKRS'],
				'MENGE1' => intval($_sapdata['MENGE1']),
				'MENGE2' => $_sapdata['MENGE2'],
				'MSTOCK1' => $_sapdata['MSTOCK1'],
				'MSTOCK2' => $_sapdata['MSTOCK2'],
				'LABST' => $_sapdata['LABST'],
				'LFGJA' => $_sapdata['LFGJA'],
				'LFMON' => $_sapdata['LFMON'],
			);
			InventoryCycleCountSapInventory::updateOrCreate(['inventory_cycle_count_id' => $val['id'],'type'=>'BEFORE'],$insertSapData);
			//更新inventory_cycle_count表的处理前数量字段
			$updateData['dispose_before_number'] = $_sapdata['MSTOCK2'];
			InventoryCycleCount::where('id',$val['id'])->update($updateData);
		}

		//插入处理后数据
		$_data = InventoryCycleCount::where('status','!=',4)->where('date','<',$today)->get()->toArray();
		foreach($_data as $key=>$val){
			$skus = array(array('MATNR'=>$val['sku']));
			$start_date = $end_date = date('Y-m-d',strtotime($val['date']));
			$sap_inventory_data = $this->getSkuInventoryBySapApi($skus,$start_date,$end_date);
			$_sapdata = $sap_inventory_data[$val['sku']][$val['factory'].'_'.$val['location']];
			$insertSapData = array(
				'inventory_cycle_count_id' => $val['id'],
				'date' => $val['date'],
				'sku' => $val['sku'],
				'factory' => $val['factory'],
				'location' => $val['location'],
				'type' => 'AFTER',
				'BUKRS' => $_sapdata['BUKRS'],
				'MENGE1' => intval($_sapdata['MENGE1']),
				'MENGE2' => $_sapdata['MENGE2'],
				'MSTOCK1' => $_sapdata['MSTOCK1'],
				'MSTOCK2' => $_sapdata['MSTOCK2'],
				'LABST' => $_sapdata['LABST'],
				'LFGJA' => $_sapdata['LFGJA'],
				'LFMON' => $_sapdata['LFMON'],
			);
			InventoryCycleCountSapInventory::updateOrCreate(['inventory_cycle_count_id' => $val['id'],'type'=>'AFTER'], $insertSapData);
			$updateData['dispose_after_number'] = $_sapdata['MSTOCK2'];//处理后数量
			InventoryCycleCount::where('id',$val['id'])->update($updateData);
		}
		Log::Info('Execution script end...');
	}

	/*
	 * 通过sku和日期，从sap接口处获得这些sku的库存数据
	 */
	public function getSkuInventoryBySapApi($skus,$start_date,$end_date)
	{
		//获取sap接口
		$skus_str = json_encode($skus);
		$array_detail['appid'] = env("SAP_KEY");
		$array_detail['method'] = 'getSkusStock';
		$array_detail['gt_table'] = $skus_str;

		$sign = $this->getSapApiSign($array_detail);
		$_sap_data = file_get_contents('http://' . env("SAP_RFC") . '/rfc_sap_api.php?appid=' . env("SAP_KEY") . '&method='.$array_detail['method'].'&skus='.$array_detail['gt_table'].'&start_date='.$start_date.'&end_date='.$end_date.'&sign=' . $sign);
		$_sap_data = json_decode($_sap_data,true);
		$sap_inventory_data = array();
		foreach($_sap_data['RESULT_TABLE'] as $key=>$val){
			$sap_inventory_data[$val['MATNR']][$val['WERKS'].'_'.$val['LGORT']] = $val;
		}
		return $sap_inventory_data;
	}

	public function getSapApiSign($array)
	{
		ksort($array);
		$authstr = "";
		foreach ($array as $k => $v) {
			$authstr = $authstr.$k.$v;
		}
		$authstr=$authstr.env("SAP_SECRET");
		$sign = strtoupper(sha1($authstr));
		return $sign;
	}


}



