<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use PDO;
use DB;
use Log;

class GetReview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:review {time}';

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
		$date_to=date('Y-m-d');	
		$words = getReviewWarnWords();
		$reviewList = DB::connection('review')->select("
		select date(tbl_review_hunter_review.review_date) as date,tbl_seller_info.amazon_account,tbl_seller_info.seller_id,tbl_review_hunter_order_product.asin,
tbl_review_hunter_order_product.sellersku,tbl_review_hunter_order_product.domain,tbl_review_hunter_review.review,
tbl_review_hunter_review.rating,tbl_amazon_customer_info.customer_email,tbl_review_hunter_review.content,substring_index(substring_index(tbl_review_hunter_review.reviewer_name,'>',-2),'<',1) as reviewer_name
from  tbl_seller_info 
left join tbl_review_hunter_order_product using(seller_id)
left join tbl_review_hunter_review using(product_id)
left join tbl_amazon_customer_info using(customer_id)
where tbl_seller_info.user_id in (21988,34020,43704,83735,83737,83738,83739,83741,83742,83743,83744,83745,83747,83749,83750,83751,83752,83753,83754,83755,83756,83757,83758,83759,83760,83761,83762,83763,83764,83765,83766,100923,100924,100927,107260)
and tbl_review_hunter_review.review_date>=:date_from and  tbl_review_hunter_review.review_date<=:date_to 
and tbl_review_hunter_order_product.market_place_id=tbl_seller_info.marketplace_id",['date_from' => $date_from,'date_to' => $date_to]);
		
		foreach($reviewList as $review){
			$exists = DB::table('review')->where('review', $review->review)->where('site', $review->domain)->first();
			if(!$exists) {
				try{
					$insert_data = array();
					$insert_data['site'] = $review->domain;
					$insert_data['review'] = $review->review;
					$insert_data['seller_id'] = $review->seller_id;
					$insert_data['date'] = $review->date;
					$insert_data['amazon_account'] = $review->amazon_account;
					$insert_data['sellersku'] = $review->sellersku;
					$insert_data['asin'] = $review->asin;
					$insert_data['reviewer_name'] = $review->reviewer_name;
					$insert_data['review_content'] = $review->content;
					$insert_data['buyer_email'] = $review->customer_email;
					$insert_data['rating'] = $review->rating;
					$insert_data['asin_url'] = $review->domain.'/dp/'.$review->asin;
					$insert_data['status'] = 1;
					foreach($words as $word){
						if(stripos($review->content,$word) !== false) $insert_data['warn'] = 1;
					}
					$result = DB::table('review')->insert($insert_data);
				}catch (\Exception $e){
					Log::Info(' '.$review->review.' Insert Error...'.$e->getMessage());
				}
			}else{
				Log::Info(' '.$review->review.' AlReady Exists...');
			}
				
    	}
	}

}
