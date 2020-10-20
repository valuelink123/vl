<?php

namespace App\Models;
use App\Services\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use App\SapPurchase;
use App\SapShipment;
use App\SapAsinMatchSku;
use App\ShipmentRequest;
use App\AsinSalesPlan;
use App\Models\AsinPlansPlan;
class AsinData extends Model
{
	//
	use  ExtendedMysqlQueries;
	protected $connection = 'amazon';
	protected $table = 'asin_data';

	protected $guarded = [];
	public $timestamps = false;

	static function calPlans($type,$asin,$marketplace_id,$sku,$date_from,$date_to)
	{
		$estimated_shipment_datas = ShipmentRequest::selectRaw("sum(quantity) as quantity,subdate(received_date,date_format(received_date,'%w')-7) as date")->where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('received_date','>=',$date_from)->where('received_date','<=',$date_to)->where('shipment_completed',0)->whereNotNull('shipment_id')->where('status','<>',4)->groupBy(['date'])->pluck('quantity','date');//以周天数据为维度汇总//在途数据

		$estimated_purchase_datas = SapPurchase::selectRaw("sum(quantity) as quantity,subdate(estimated_delivery_date,date_format(estimated_delivery_date,'%w')-7) as date")->where('sku',$sku)->where('estimated_delivery_date','>=',$date_from)->where('estimated_delivery_date','<=',$date_to)->whereNull('actual_delivery_date')->groupBy(['date'])->pluck('quantity','date');//以周天数据为维度汇总

		$_data = array();
		$field = 'quantity_miss_sale';
		//$type==1为sale的预测，$type=2为计划预测数据,导入数据的时候，状态默认为0，所以此处没有限制状态
		if($type==1){
			$_data = AsinSalesPlan::where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('week_date','>=',$date_from)->where('week_date','<=',$date_to)->pluck('quantity_last','week_date');
		}
		if($type==2){
			$_data = AsinPlansPlan::where('asin',$asin)->where('marketplace_id',$marketplace_id)->where('week_date','>=',$date_from)->where('week_date','<=',$date_to)->pluck('quantity_last','week_date');
			$field = 'quantity_miss_plan';
		}

		$data = [];
		$quantity_miss = 0;
		$requestTime = date('Y-m-d H:i:s');
		foreach($_data as $key=>$val){
			$quantity_miss += (intval($val)-intval(array_get($estimated_shipment_datas,$key)));
			$data[] = array(
				'asin'=>$asin,
				'marketplace_id'=>$marketplace_id,
				'sku'=>$sku,
				'week_date'=>$key,
				'estimated_afn'=>intval(array_get($estimated_shipment_datas,$key)),
				'estimated_purchase'=>intval(array_get($estimated_purchase_datas,$key)),
				$field=>$quantity_miss,
				'updated_at'=>$requestTime
			);
		}

		if($data){
			AsinData::insertOnDuplicateWithDeadlockCatching($data, ['week_date','estimated_afn','estimated_purchase',$field,'updated_at']);
		}
	}
}
