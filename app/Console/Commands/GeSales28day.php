<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use App\Sales28day;
use App\Asin;
use PDO;
use DB;
use Log;

class GetSales28day extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:dailysales {before}';

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
		$before =  abs(intval($this->argument('before')));
        if(!$before) $before = '1';
		while($before>=1){
			$date=date('Y-m-d',strtotime('-'.$before.' days'));
			$sales = DB::connection('order')->select('select sum(quantityordered) as sale,sellersku,marketplaceid from amazon_orders_item where AmazonOrderId in (select AmazonOrderId from amazon_orders where left(PurchaseDate,10)=:date)
 group by sellersku,marketplaceid',['date' => $date]);
			foreach($sales as $sale){
				Sales28day::updateOrCreate(
				[
					'seller_sku' => $sale->sellersku,
					'site_id' => array_get(matchMarketplaceSiteCode(),$sale->marketplaceid,''),
					'date'=> $date],[
					'qty' => intval($sale->sale)
				]);
			}
			$before--;
		}
		Sales28day::where('date','<',date('Y-m-d',strtotime('-30 days')))->delete();
		DB::update("update asin set sales_28_22 = (select sum(qty) from sales_28_day where date BETWEEN '".date('Y-m-d',strtotime('-28 days'))."' and '".date('Y-m-d',strtotime('-22 days'))."'
and asin.sellersku=sales_28_day.seller_sku and asin.sap_site_id=sales_28_day.site_id),sales_21_15 = (select sum(qty) from sales_28_day where date BETWEEN '".date('Y-m-d',strtotime('-21 days'))."' and '".date('Y-m-d',strtotime('-15 days'))."'
and asin.sellersku=sales_28_day.seller_sku and asin.sap_site_id=sales_28_day.site_id),sales_14_08 = (select sum(qty) from sales_28_day where date BETWEEN '".date('Y-m-d',strtotime('-14 days'))."' and '".date('Y-m-d',strtotime('-8 days'))."'
and asin.sellersku=sales_28_day.seller_sku and asin.sap_site_id=sales_28_day.site_id),sales_07_01 = (select sum(qty) from sales_28_day where date BETWEEN '".date('Y-m-d',strtotime('-7 days'))."' and '".date('Y-m-d',strtotime('-1 days'))."'
and asin.sellersku=sales_28_day.seller_sku and asin.sap_site_id=sales_28_day.site_id)");
		
		
		
	}

}
