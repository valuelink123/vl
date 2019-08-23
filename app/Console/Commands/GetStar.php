<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Star;
use App\Starhistory;
use App\Listinghistory;
use PDO;
use DB;
use Log;

class GetStar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:star {time}';

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
        $time =  $this->argument('time');
        if(!$time) $time = '3days';
		$date_from=date('Y-m-d',strtotime('-'.$time));		

		
		$reviewList = DB::connection('review_new')->select('select * from tbl_star_system_star_info
where create_at>:date_from',['date_from' => $date_from]);
		
		foreach($reviewList as $review){

			try{
				$data = array(
					'asin' => $review->asin,
					'sellersku' => '',
					'domain' => $review->domain,
					'one_star_number' => $review->one_star_number,
					'two_star_number' => $review->two_star_number,
					'three_star_number' => $review->three_star_number,
					'four_star_number' => $review->four_star_number,
					'five_star_number' => $review->five_star_number,
					'total_star_number' => $review->total_star_number,
					'average_score' => $review->average_score,
					'create_at' => substr($review->create_at,0,10));
				$star = Star::where('asin',$review->asin)->where('domain',$review->domain)->first();
				if($star){
					if(substr($review->create_at,0,10)>$star->create_at){
						Star::where('asin',$review->asin)->where('domain',$review->domain)->update($data);
					}
				}else{
					Star::insert($data);
				}
				
				$star = Starhistory::where('asin',$review->asin)->where('domain',$review->domain)->where('create_at',substr($review->create_at,0,10))->first();
				if(!$star){
					Starhistory::insert($data);
				}
				
			}catch (\Exception $e){
				Log::Info($e->getMessage());
			}	
    	}
		
		
		$sales =  DB::connection('order')->select('select sum(quantityordered) as sales,sum(ItemPriceAmount) as amount,asin,amazon_orders_item.marketplaceid,
left(LastUpdateDate,10) as date from amazon_orders_item left join amazon_orders on amazon_orders_item.AmazonOrderId=amazon_orders.AmazonOrderId and amazon_orders_item.SellerId=amazon_orders.SellerId where left(LastUpdateDate,10)>:date_from group by asin,amazon_orders_item.marketplaceid,date',['date_from' => $date_from]);
		$sales_value=[];
		foreach($sales as $sale){
			$sales_value[$sale->asin]['www.'.array_get(getSiteUrl(),$sale->marketplaceid)][$sale->date]=['sales'=>rount($sale->sales,2),'amount'=>rount($sale->amount,2)];
		}
 
 
		$lists = DB::connection('review_new')->select('select * from tbl_star_system_product
where last_updated>:date_from',['date_from' => $date_from]);
		$patterns = '/\d+[\.,]?\d+(%)?/is';
		foreach($lists as $list){
			
			$coupon_n=$coupon_p=0;
			$price = $status = 0;
			if($list->product_status=='available') $status = 2;
			if($list->product_status=='unavailable') $status = 1;
			if($list->price){
				if(in_array($list->domain,array('www.amazon.ca','www.amazon.com','www.amazon.co.jp','www.amazon.co.uk'))){
					$price = round($list->price/100,2);
				}else{
					$price = round($list->price/10000,2);
				}
			}
			if($list->coupon){
				preg_match($patterns, $list->coupon,$arr);
				if($arr){
					$arr_val = str_replace([',','%'],['.',''],$arr[0]);
					if(array_get($arr,'1')=='%'){
						$coupon_p=round($arr_val,2);
						$coupon_n=round($price*$coupon_p/100);
					}else{
						$coupon_n=round($arr_val,2);
						$coupon_p=round($coupon_n/$price,2)*100;
					}
				}
			}
			Listinghistory::updateOrCreate([
				'asin' => $list->asin,
				'domain' => $list->domain,
				'date' => substr($list->last_updated,0,10)
			],
			[
				'coupon_n' => $coupon_n,
				'coupon_p' => $coupon_p,
				'sales' => array_get($sales_value,$list->asin.'.'.$list->domain.'.'.substr($list->last_updated,0,10).'.sales',0),
				'amount' => array_get($sales_value,$list->asin.'.'.$list->domain.'.'.substr($list->last_updated,0,10).'.amount',0),
				'price' => $price,
				'status' => $status
			]);

    	}
	}

}
