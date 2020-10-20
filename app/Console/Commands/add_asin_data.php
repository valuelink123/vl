<?php
/* Date: 2019.9.21
 * Author: wulanfnag
 * 添加asindata数据
 * 每天跑一次此处理
 * */
namespace App\Console\Commands;

use App\AsinSalesPlan;
use Illuminate\Console\Command;
use DB;
use Log;
use App\Models\AsinData;
use App\ShipmentRequest;
use App\SapPurchase;
use App\Models\AsinPlansPlan;

class AddAsinData extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'add:asin_data';

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
		$date = date('Y-m-d');
		Log::Info('Add asin_data Start...');
		DB::connection()->enableQueryLog(); // 开启查询日志

		$date_last = date('Y-m-d',strtotime($date.'-1 weeks sunday'));//上周末的日期
		$date_from = date('Y-m-d',strtotime($date_last)-86400*6);//上周一
		$date_to = date('Y-m-d',strtotime($date.'+0 weeks sunday'));//这周末的日期

		$estimated_shipment_datas = ShipmentRequest::selectRaw("sum(quantity) as quantity,concat(asin,'_',marketplace_id,'_',subdate(received_date,date_format(received_date,'%w')-7))  as amdate")->where('received_date','>=',$date_from)->where('received_date','<=',$date_to)->where('shipment_completed',0)->whereNotNull('shipment_id')->where('status','<>',4)->groupBy(['amdate'])->pluck('quantity','amdate');//以周天数据为维度汇总//在途数据

		$estimated_purchase_datas = SapPurchase::selectRaw("sum(quantity) as quantity,concat(sku,'_',subdate(estimated_delivery_date,date_format(estimated_delivery_date,'%w')-7)) as sdate")->where('estimated_delivery_date','>=',$date_from)->where('estimated_delivery_date','<=',$date_to)->whereNull('actual_delivery_date')->groupBy(['sdate'])->pluck('quantity','sdate');//以周天数据为维度汇总

		$saleData = AsinSalesPlan::select('asin','marketplace_id','sku','quantity_last','week_date')->where('week_date','>=',$date_from)->where('week_date','<=',$date_to)->get();
		$planData = AsinPlansPlan::select('asin','marketplace_id','sku','quantity_last','week_date')->where('week_date','>=',$date_from)->where('week_date','<=',$date_to)->get();

		$requestTime = date('Y-m-d H:i:s');
		$arrayData = array('quantity_miss_sale'=>$saleData,'quantity_miss_plan'=>$planData);//放在一个数组里面，好遍历循环处理
		foreach($arrayData as $field=>$_data){
			$data = [];
			foreach($_data as $key=>$val){
				$amdate = $val->asin.'_'.$val->marketplace_id.'_'.$val->week_date;
				$quantity_miss[$amdate] = 0;
				$sdate = $val->sku.'_'.$val->week_date;
				$quantity_miss[$amdate] += (intval($val->quantity_last)-intval(array_get($estimated_shipment_datas,$amdate)));//缺失数量是以asin+站点+week_date为维度的
				$data[] = array(
					'asin'=>$val->asin,
					'marketplace_id'=>$val->marketplace_id,
					'sku'=>$val->sku,
					'week_date'=>$val->week_date,
					'estimated_afn'=>intval(array_get($estimated_shipment_datas,$amdate)),
					'estimated_purchase'=>intval(array_get($estimated_purchase_datas,$sdate)),
					$field=>$quantity_miss[$amdate],
					'updated_at'=>$requestTime
				);
			}
			if($data){
				AsinData::insertOnDuplicateWithDeadlockCatching($data, ['week_date','sku','estimated_afn','estimated_purchase',$field,'updated_at']);
			}
		}
		Log::Info('Execution script end...');

	}


}



