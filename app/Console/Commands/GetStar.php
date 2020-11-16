<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Asin;
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
		$sales =  DB::select('select sum(sales) as sales,sum(amount) as amount,asin,site,
date from asin_daily_info where date>:date_from group by asin,site,date',['date_from' => $date_from]);
		
		foreach($sales as $sale){
			$asin_value[$sale->asin][str_replace('.','',$sale->site)][$sale->date]['sales']=round($sale->sales,2);
			$asin_value[$sale->asin][str_replace('.','',$sale->site)][$sale->date]['amount']=round($sale->amount,2);
		}
 
 
		$lists = DB::connection('review_new')->select('select * from tbl_star_system_product
where last_updated>:date_from',['date_from' => $date_from]);
//		$patterns = '/\d+[\.,]?\d+(%)?/is';
        //修正正则表达式，以匹配百分比为个位数(如5%)以及数字大于999（如1,000.95或者10,100.95）的匹配,
        $patterns='/((\d+[\.|\,]?)+\d+(%)?)|(\d+(\.\d+)?%)/';
		foreach($lists as $list){
            //[coupon_p:折扣率百分比，coupon_n:折扣金额具体数值]
			$coupon_n = 0.00;
            $coupon_p = 0.00;
			$price = 0.00;
            $status = 0;
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
					$arr_val = str_replace([',','%'],['',''],$arr[0]);
					//判断coupon是百分比还是具体数值
                    if(strpos($arr[0],'%')){
                        //百分比
                        $coupon_p=round($arr_val,2);
                        $coupon_n=round($price*$coupon_p/100,2);
                    }else{
                        //具体数值
                        $coupon_n=round($arr_val,2);
                        $coupon_p=($price)?round($coupon_n/$price,4)*100:0.00;
                    }
//					if(array_get($arr,'0')=='%'){
//						$coupon_p=round($arr_val,2);
//						$coupon_n=round($price*$coupon_p/100);
//					}else{
//						$coupon_n=round($arr_val,2);
//						$coupon_p=($price)?round($coupon_n/$price,2)*100:0;
//					}
				}
			}
			
			$asin_value[$list->asin][str_replace('.','',$list->domain)][substr($list->last_updated,0,10)]['coupon_n']=$coupon_n;
			$asin_value[$list->asin][str_replace('.','',$list->domain)][substr($list->last_updated,0,10)]['coupon_p']=$coupon_p;
			$asin_value[$list->asin][str_replace('.','',$list->domain)][substr($list->last_updated,0,10)]['price']=$price;
			$asin_value[$list->asin][str_replace('.','',$list->domain)][substr($list->last_updated,0,10)]['status']=$status;

    	}
		
		$reviewList = DB::connection('review_new')->select('select * from tbl_star_system_star_info where create_at>:date_from',['date_from' => $date_from]);
		
		foreach($reviewList as $review){
			$asin_value[$review->asin][str_replace('.','',$review->domain)][substr($review->create_at,0,10)]['one_star_number']=$review->one_star_number;
			$asin_value[$review->asin][str_replace('.','',$review->domain)][substr($review->create_at,0,10)]['two_star_number']=$review->two_star_number;
			$asin_value[$review->asin][str_replace('.','',$review->domain)][substr($review->create_at,0,10)]['three_star_number']=$review->three_star_number;
			$asin_value[$review->asin][str_replace('.','',$review->domain)][substr($review->create_at,0,10)]['four_star_number']=$review->four_star_number;
			$asin_value[$review->asin][str_replace('.','',$review->domain)][substr($review->create_at,0,10)]['five_star_number']=$review->five_star_number;
			$asin_value[$review->asin][str_replace('.','',$review->domain)][substr($review->create_at,0,10)]['total_star_number']=$review->total_star_number;
			$asin_value[$review->asin][str_replace('.','',$review->domain)][substr($review->create_at,0,10)]['average_score']=$review->average_score;
			if( substr($review->create_at,0,10)>=date('Y-m-d',strtotime('-1day')) ){
				DB::table('asin')->where('asin',$review->asin)->where('site',$review->domain)->update(['rating'=>round($review->average_score,2),'review_count'=>intval($review->total_star_number)]);
			}	
    	}
		
		
		
		$asins = DB::select('select asin,site from asin group by asin,site');
		foreach($asins as $asin){
			if(!array_get($siteToCcpMid,$asin->site)) continue;
			$ccp_datas = DB::connection("ccp")->select(
				"select products.asin,
				max(product_performances.sessions) as sessions,
				max(product_performances.unit_session_percentage) as unit_session_percentage,
				min(products.bsr) as bsr,
				product_performances.report_date 
				from accounts 
				left join products on products.account_id=accounts.id 
				left join product_performances on products.id=product_performances.product_id 
				where  product_performances.report_date>='".$date_from."' and asin='".$asin->asin."' 
				and marketplace_id=".array_get($siteToCcpMid,$asin->site)." GROUP BY products.asin,product_performances.report_date;"
			);
			foreach($ccp_datas as $ccp_data){
				$asin_value[$asin->asin][str_replace('.','',$asin->site)][$ccp_data->report_date]['sessions']=round($ccp_data->sessions,2);
				$asin_value[$asin->asin][str_replace('.','',$asin->site)][$ccp_data->report_date]['unit_session_percentage']=round($ccp_data->unit_session_percentage,2);
				$asin_value[$asin->asin][str_replace('.','',$asin->site)][$ccp_data->report_date]['bsr']=intval($ccp_data->bsr,2);
			}
			
			$asin_data = array_get($asin_value,$asin->asin.'.'.str_replace('.','',$asin->site));
			if(is_array($asin_data)){
				foreach($asin_data as $k=>$v){
					try{
						Starhistory::updateOrCreate(
							[
							'asin' => $asin->asin,
							'domain' => $asin->site,
							'create_at' => $k
							],
							$v
						);
					}catch (\Exception $e){
						Log::Info($e->getMessage());
					}	
				
				}
			}
			$yesterday_star = Starhistory::where('asin',$asin->asin)->where('domain',$asin->site)->where('create_at',date('Y-m-d',strtotime('-1day')))->get(['one_star_number','two_star_number','three_star_number','four_star_number','five_star_number','total_star_number','average_score'])->first();
			if(!empty($yesterday_star) && $yesterday_star->average_score==0){
				$updateData = Starhistory::where('asin',$asin->asin)->where('domain',$asin->site)->where('create_at',date('Y-m-d',strtotime('-2day')))->get(['one_star_number','two_star_number','three_star_number','four_star_number','five_star_number','total_star_number','average_score'])->first();
				if(!empty($updateData))  Starhistory::where('asin',$asin->asin)->where('domain',$asin->site)->where('create_at',date('Y-m-d',strtotime('-1day')))->update($updateData->toArray());
			}
		}	
	}	
}
