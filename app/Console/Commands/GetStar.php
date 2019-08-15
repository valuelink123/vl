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
		
		
		
		
		$asinList = DB::connection('review_new')->select('select * from tbl_star_system_product
where last_updated>:date_from',['date_from' => $date_from]);
		$patterns = '/\d+[\.,]?\d+(%)?/is';
		foreach($asinList as $asin){
			$coupon_n=$coupon_p=0;
			$price = 0;
			$status = ($asin->product_status=='available')?1:0;
			if($asin->price){
				if(in_array($asin->domain,array('www.amazon.ca','www.amazon.com'))){
					$price = round($asin->price/100,2);
				}else{
					$price = round($asin->price/10000,2);
				}
			}
			if($asin->coupon){
				preg_match($patterns, $asin->coupon,$arr);
				if($arr){
					$arr_val = str_replace([',','%'],['.'.''],$arr[0]);
					if(array_get($arr,'1')=='%'){
						$coupon_p=round($arr_val,2);
						$coupon_n=round($price*$coupon_p/100);
					}else{
						$coupon_n=round($arr_val,2);
						$coupon_p=round($coupon_n/$price,2);
					}
				}
			}

			Listinghistory::updateOrCreate([
				'asin' => $asin->asin,
				'domain' => $asin->domain,
				'date' => substr($asin->last_updated,0,10)
			],
			[
				'coupon_n' => $coupon_n,
				'coupon_p' => $coupon_p,
				'price' => $price,
				'status' => $status
			]);

    	}
	}

}
