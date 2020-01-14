<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use PDO;
use DB;
use Log;

class GetRequestReviewTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:requestreviewtasks';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$accounts_config = [];
		$accounts  = DB::connection('amazon')->table('seller_accounts')->get();
		foreach($accounts as $account){
			$accounts_config[$account->id]['mws_seller_id'] = $account->mws_seller_id;
			$accounts_config[$account->id]['mws_marketplaceid'] = $account->mws_marketplaceid;
		}
		$asins  = DB::table('auto_request_asin')->get();
		$date = date('Y-m-d',strtotime('-7days'));
		foreach($asins as $asin){
			$sales_channel = str_replace('www.','',$asin->site);
			$orders = DB::connection('amazon')->select("select * from orders where order_status='Shipped' and fulfillment_channel='AFN' and  date(last_update_date)='$date' and sales_channel='$sales_channel' and asins like '%".$asin->asin."*%'");
			foreach($orders as $order){
				try{
					$sellerid = array_get($accounts_config,$order->seller_account_id.'.mws_seller_id');
					$marketplaceid = array_get($accounts_config,$order->seller_account_id.'.mws_marketplaceid');
					$exists_feedback = DB::connection('order')->table('amazon_feedback')->where('AmazonOrderId',$order->amazon_order_id)->value('AmazonOrderId');
					if($exists_feedback) continue;
					$exists_refund = DB::connection('order')->table('finances_refund_event')->where('AmazonOrderId',$order->amazon_order_id)->value('amazon_order_id');
					if($exists_refund) continue;
					$exists_exception = DB::table('exception')->where('amazon_order_id',$order->amazon_order_id)->whereIn('type',[1,2])->value('amazon_order_id');
					if($exists_exception) continue;
					$exists = DB::table('auto_request_review')->where('sellerid',$sellerid)->where('AmazonOrderId',$order->amazon_order_id)->value('AmazonOrderId');
					if($exists) continue;
					DB::table('auto_request_review')->insert([
						'SellerId'=>$sellerid,
						'AmazonOrderId'=>$order->amazon_order_id,
						'MarketPlaceId'=>$marketplaceid,
						'asin'=>$asin->asin,
						'site'=>$asin->site,
						'sap_seller_id'=>$asin->sap_seller_id,
						'created_at'=>date('Y-m-d H:i:s'),
						'updated_at'=>date('Y-m-d H:i:s')
					]);
				} catch (\Exception $e) {
					continue;
				}
			}	
		}
	}	
}
