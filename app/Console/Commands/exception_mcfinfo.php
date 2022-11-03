<?php
/*
 * 获取异常单的mcf物流信息
 */
namespace App\Console\Commands;

use App\Templates;
use Illuminate\Console\Command;
use DB;
use Log;
use App\Sendbox;

class ExceptionMcfInfo extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'exception:mcfInfo';

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
//	public function __construct()
//	{
//		parent::__construct();
//		echo 444;
//	}

	/*
	 *
	 */
	function handle()
	{
		set_time_limit(0);
		$today = date('Y-m-d H:i:s');
		$msg =  'Execution exception:mcfInfo script start time:'.$today."\n";
		Log::Info($msg);
		$replacement_order_ids = array();
		$opera_data = array();
		//当类型为exception和gift的时候才有
		//replacement=1,refund=2,gift card=2,,and brand is not null
		$sql = "select id,type,replacement,brand,mcf_info,mcf_sendbox_id from exception where (type=2 || type=4) and mcf_sendbox_id is null and replacement is not null and customer_email is not null and brand is not null";
		$_data = DB::select($sql);

		foreach($_data as $key=>$val){
			$replacements = unserialize($val->replacement);
			$products = array_get($replacements,'products',array());
			if(is_array($products)){
				foreach( $products as $product){
					$replacement_order_id = array_get($product,'replacement_order_id');
					if($replacement_order_id){
						$replacement_order_ids[] = $replacement_order_id;
						$opera_data[$replacement_order_id][] = $val->id;
					}
				}
			}
		}
		$replacement_order_ids = array_chunk($replacement_order_ids,1000);

		$gf_template = Templates::find(5164);//发邮件用的模板信息
		foreach($replacement_order_ids as $order_ids){
			$mcf_orders = DB::connection('amazon')->table('amazon_mcf_orders')
				->selectRaw('amazon_mcf_orders.id,amazon_mcf_orders.fulfillment_order_status,amazon_mcf_shipment_package.*')
				->leftJoin('amazon_mcf_shipment_package',function($q){
					$q->on('amazon_mcf_orders.seller_fulfillment_order_id', '=', 'amazon_mcf_shipment_package.seller_fulfillment_order_id');
				})->whereIn('amazon_mcf_orders.seller_fulfillment_order_id',$order_ids)->get();

			$mcf_orders = json_decode($mcf_orders,true);
			foreach($mcf_orders as $vmcf){
				if(isset($opera_data[$vmcf['seller_fulfillment_order_id']])) {
					$_exceptionIds = $opera_data[$vmcf['seller_fulfillment_order_id']];
					//更改mcf_info信息
					DB::table('exception')->whereIn('id', $_exceptionIds)->update(['mcf_info' => json_encode($vmcf)]);

					//一个重发单号对应的几个异常单数据，遍历循环依次发邮件
					foreach($_exceptionIds as $_exceptionId) {
						$exceptionData = DB::table('exception')->where('id', $_exceptionId)->first();
						//发送邮件给客户
						if (empty($gf_template)) throw new \Exception('Set Failed, Template not exists!');
						$subject = str_replace("{BRAND}", $exceptionData->brand, $gf_template->title);

						$content = str_replace("{BRAND}", $exceptionData->brand, $gf_template->content);
						$content = str_replace("{BRAND_LINK}", array_get(getBrands(), $exceptionData->brand . '.url'), $content);
						$content = str_replace("{CUSTOMER_NAME}", $exceptionData->name, $content);
						$content = str_replace("{CARRIER_CODE}", $vmcf['carrier_code'], $content);
						$content = str_replace("{TRACKING_NUMBER}", $vmcf['tracking_number'], $content);
						$content = str_replace("{ORDER_NUMBER}", $exceptionData->amazon_order_id, $content);
						$insertSendbox = array(
							'user_id' => 1,
							'from_address' => array_get(getBrands(), $exceptionData->brand . '.email'),
							'to_address' => $exceptionData->customer_email,
							'subject' => $subject,
							'text_html' => $content,
							'date' => date('Y-m-d H:i:s'),
							'plan_date' => 0,
							'status' => 'Waiting',
							'inbox_id' => 0,
							'warn' => 0,
							'ip' => 0,
							'attachs' => NULL,
							'error' => NULL,
							'error_count' => 0,
							'features' => 1,
						);
						$sendbox_id = DB::table('sendbox')->insertGetId($insertSendbox);
						DB::table('exception')->where('id', $_exceptionId)->update(['mcf_sendbox_id' => $sendbox_id]);
					}
				}
			}
		}
	}
}



