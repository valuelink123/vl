<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Star;
use App\Starhistory;
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

		
		$reviewList = DB::connection('review')->select('select tbl_star_system_star_info.*,tbl_star_system_product.seller_sku from tbl_star_system_star_info left join tbl_star_system_product on tbl_star_system_star_info.product_id=tbl_star_system_product.product_id  
where tbl_star_system_star_info.create_at>:date_from',['date_from' => $date_from]);
		
		foreach($reviewList as $review){

			try{
				$data = array(
					'asin' => $review->asin,
					'sellersku' => $review->seller_sku,
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
				print_r( $e->getMessage() );
			}	
    	}
	}

}
