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
		$siteToCcpMid = array(
			'www.amazon.com'=>1,
			'www.amazon.ca'=>2,
			'www.amazon.de'=>4,
			'www.amazon.es'=>5,
			'www.amazon.fr'=>6,
			'www.amazon.it'=>8,
			'www.amazon.co.uk'=>9,
			'www.amazon.co.jp'=>10
			
		);
		
		$asin_value=[];
		$sales =  DB::connection('order')->select('select sum(quantityordered) as sales,sum(ItemPriceAmount) as amount,asin,amazon_orders_item.marketplaceid,
left(LastUpdateDate,10) as date from amazon_orders_item left join amazon_orders on amazon_orders_item.AmazonOrderId=amazon_orders.AmazonOrderId and amazon_orders_item.SellerId=amazon_orders.SellerId where left(LastUpdateDate,10)>:date_from group by asin,amazon_orders_item.marketplaceid,date',['date_from' => $date_from]);
		
		foreach($sales as $sale){
			$asin_value[$sale->asin][str_replace('.','','www.'.array_get(getSiteUrl(),$sale->marketplaceid))][$sale->date]['sales']=round($sale->sales,2);
			$asin_value[$sale->asin][str_replace('.','','www.'.array_get(getSiteUrl(),$sale->marketplaceid))][$sale->date]['amount']=round($sale->amount,2);
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
						$coupon_p=($price)?round($coupon_n/$price,2)*100:0;
					}
				}
			}
			
			$asin_value[$list->asin][str_replace('.','',$list->domain)][substr($list->last_updated,0,10)]['coupon_n']=$coupon_n;
			$asin_value[$list->asin][str_replace('.','',$list->domain)][substr($list->last_updated,0,10)]['coupon_p']=$coupon_p;
			$asin_value[$list->asin][str_replace('.','',$list->domain)][substr($list->last_updated,0,10)]['price']=$price;
			$asin_value[$list->asin][str_replace('.','',$list->domain)][substr($list->last_updated,0,10)]['status']=$status;

    	}
		
		$reviewList = DB::connection('review_new')->select('select * from tbl_star_system_star_info where create_at>:date_from',['date_from' => $date_from]);
		
		foreach($reviewList as $review){
			try{
				$data = array(
					'one_star_number' => $review->one_star_number,
					'two_star_number' => $review->two_star_number,
					'three_star_number' => $review->three_star_number,
					'four_star_number' => $review->four_star_number,
					'five_star_number' => $review->five_star_number,
					'total_star_number' => $review->total_star_number,
					
					'sales' => array_get($asin_value,$review->asin.'.'.str_replace('.','',$review->domain).'.'.substr($review->create_at,0,10).'.sales',0),
					'amount' => array_get($asin_value,$review->asin.'.'.str_replace('.','',$review->domain).'.'.substr($review->create_at,0,10).'.amount',0),
					'coupon_n' => array_get($asin_value,$review->asin.'.'.str_replace('.','',$review->domain).'.'.substr($review->create_at,0,10).'.coupon_n',0),
					'coupon_p' => array_get($asin_value,$review->asin.'.'.str_replace('.','',$review->domain).'.'.substr($review->create_at,0,10).'.coupon_p',0),
					'price' => array_get($asin_value,$review->asin.'.'.str_replace('.','',$review->domain).'.'.substr($review->create_at,0,10).'.price',0),
					'status' => array_get($asin_value,$review->asin.'.'.str_replace('.','',$review->domain).'.'.substr($review->create_at,0,10).'.status',0),
					
					
					'average_score' => $review->average_score
				);
				
				$ccp_datas = DB::connection("ccp")->select(
                    "select products.asin,
					max(product_performances.sessions) as sessions,
					max(product_performances.unit_session_percentage) as unit_session_percentage,
					min(products.bsr) as bsr,
					product_performances.report_date 
					from accounts 
					left join products on products.account_id=accounts.id 
					left join product_performances on products.id=product_performances.product_id 
					where  product_performances.report_date='".substr($review->create_at,0,10)."' and asin='".$review->asin."' 
					and marketplace_id='".array_get($siteToCcpMid,$review->domain)."' GROUP BY products.asin,product_performances.report_date;"
                    );
				foreach($ccp_datas as $ccp_data){
					$data['sessions']=round($ccp_data->sessions,2);
					$data['unit_session_percentage']=round($ccp_data->unit_session_percentage,2);
					$data['bsr']=intval($ccp_data->bsr);
				}
				
		
				
				
				
				Starhistory::updateOrCreate(
					[
					'asin' => $review->asin,
					'domain' => $review->domain,
					'create_at' => substr($review->create_at,0,10)
					]
					,
					$data
				);
			}catch (\Exception $e){
				Log::Info($e->getMessage());
			}	
    	}
		
		

		
		
	}
	

}
