<?php
/*
 *更新amazon_mcf_orders表的amazon_order_id字段
 * amazon_order_id字段为重发单的原始订单号，RR功能有原始订单和绑定的重发单，根据RR功能中绑定的重发单找到其原始订单
 * 只查一次历史的数据就可以，之后的数据可在RR功能中有新增更改的时候就相应的更改
 * 此脚本每个月执行一次
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;


class McfOrderUpdateAmazonOrderId extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'update:mcf_order_amazonorderid';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

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

	//更新amazon_mcf_orders表的sap_status字段和amazon_order_id字段
	function handle()
	{
		$num = 200;
		set_time_limit(0);
		$today = date('Y-m-d H:i:s');
		$msg =  'Execution mcf_order_amazonorderid.php script start time:'.$today."\n";
		Log::Info($msg);
		DB::connection('amazon')->enableQueryLog(); // 开启查询日志
		//找到重发单的原始订单号，更新amazon_order_id字段
		$this->updateAmazonOrderId();

		$queries = DB::connection('amazon')->getQueryLog(); // 获取查询日志
//		Log::Info($queries); // 即可查看执行的sql，传入的参数等等
		echo 'Execution script end';
	}
	//找到重发单的原始订单号，更新amazon_order_id字段
	function updateAmazonOrderId()
	{
		$sql = 'select amazon_order_id,replacement 
				from exception 
				where type in (2,3) 
				and process_status != "cancel" 
				and length(replacement) >5 
				order BY date desc';
		$exceptionData = DB::select($sql);
		$replacementOrderID = array();//重发单号跟原始单号的一一对应关系
		foreach ( $exceptionData as $ek=>$ev){
			$replacements = unserialize($ev->replacement);
			$products = array_get($replacements,'products',array());
			if(is_array($products)){
				foreach( $products as $product){
					//replacement_order_id为重发单
					$orderid = isset($product['replacement_order_id']) ? $product['replacement_order_id'] : '-';
					$replacementOrderID[$orderid] = $ev->amazon_order_id;
				}
			}
		}
		//把重发单号分批次处理,一次处理num个，根据seller_fulfillment_order_id订单号是重发单号，对应将原始订单号插入到amazon_mcf_orders表的amazon_order_id字段中
		$num = 500;//每次操作500条数据
		$mcfOrderIDs = array_chunk(array_keys($replacementOrderID),$num);
		foreach($mcfOrderIDs as $mcfOrderID){
			$mcfData = DB::connection('amazon')->table('amazon_mcf_orders')->select('id','seller_fulfillment_order_id')->whereIn('seller_fulfillment_order_id',$mcfOrderID)->get()->toArray();
			$update = array();
			foreach($mcfData as $mk=>$mv){
				if(isset($replacementOrderID[$mv->seller_fulfillment_order_id]) && $replacementOrderID[$mv->seller_fulfillment_order_id]){
					$update[] = array(
						'id' => $mv->id,
						'amazon_order_id' => $replacementOrderID[$mv->seller_fulfillment_order_id]
					);
				}
			}
			$res = updateBatch('amazon','amazon_mcf_orders',$update);
		}
		Log::Info($res);
		return true;
	}
}



