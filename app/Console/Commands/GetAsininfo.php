<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use PDO;
use DB;
use Log;

class GetAsininfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:asininfo';

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
	
		$data = $sales_t = $stock_t=[];
		$site_marketplaceid=siteToMarketplaceid();
		$marketplaceid_area=array(
			'A2EUQ1WTGCTBG2'=>'US',
			'A1PA6795UKMFR9'=>'EU',
			'A1RKKUPIHCS9HS'=>'EU',
			'A13V1IB3VIYZZH'=>'EU',
			'APJ6JRA9NG5V4'=>'EU',
			'A1VC38T7YXB528'=>'JP',
			'A1F83G8C2ARO7P'=>'EU',
			'A1AM78C64UM0Y8'=>'US',
			'ATVPDKIKX0DER'=>'US'
		);
		$appkey = 'site0001';
		$appsecret= 'testsite0001';
		
		$array['asin']='';
		$array['appid']= $appkey;
		$array['method']='getAsinInfo';
		ksort($array);
		$authstr = "";
		foreach ($array as $k => $v) {
			$authstr = $authstr.$k.$v;
		}
		$authstr=$authstr.$appsecret;
		$sign = strtoupper(sha1($authstr));

		$res = file_get_contents('http://116.6.105.153:18003/rfc_site.php?appid='.$appkey.'&method='.$array['method'].'&asin='.$array['asin'].'&sign='.$sign);
		$result = json_decode($res,true);
		$asinList = array_get($result,'data');
		$exits_asin=[];
		DB::table('fbm_stock')->truncate();
		$sku_fbm_stock=[];
		foreach($asinList as $asin){
			$sku_fbm_stock[array_get($asin,'MATNR','')] = array(
				'item_name'=>array_get($asin,'MAKTX'),
				'cost'=> array_get($asin,'VERPR_FBA',0),
				'fbm_stock'=> array_get($asin,'LBKUM_FBM',0),
				'fbm_amount'=> array_get($asin,'SALK3_FBM',0),
			);
			$exists = DB::table('fbm_stock')->where('item_code',array_get($asin,'MATNR',''))->get()->toArray();
			if(!$exists){
				DB::table('fbm_stock')->insert(
					array(
						'item_code'=>array_get($asin,'MATNR'),
						'item_name'=>array_get($asin,'MAKTX'),
						'cost'=>array_get($asin,'VERPR_FBA',0),
						'fbm_stock'=>array_get($asin,'LBKUM_FBM',0),
						'fbm_amount'=>array_get($asin,'SALK3_FBM',0),
						'updated_at'=>date('Y-m-d'),
					)
				);
			}
			
		}
		
		$date_from = date('Y-m-d',strtotime('-15 day')).' 00:00:00';
		$date_to = date('Y-m-d',strtotime('-1 day')).' 23:59:59';
		$sales = DB::connection('order')->select('select sum(quantityordered) as sale,asin,marketplaceid from amazon_orders_item where AmazonOrderId in (select AmazonOrderId from amazon_orders where PurchaseDate>=:date_from and PurchaseDate<=:date_to)
 group by asin,marketplaceid',['date_from' => $date_from,'date_to' => $date_to]);
		foreach($sales as $sale){
			$data[$sale->asin][$sale->marketplaceid]['sales'] = round(intval($sale->sale)/14,2);
			if(!array_get($sales_t,$sale->asin.'.'.array_get($marketplaceid_area,$sale->marketplaceid).'.total_sales')) $sales_t[$sale->asin][array_get($marketplaceid_area,$sale->marketplaceid)]['total_sales']=0;
			$sales_t[$sale->asin][array_get($marketplaceid_area,$sale->marketplaceid)]['total_sales']+=round(intval($sale->sale)/14,2);
		}

		$sellerid_area=[];
		$sellerids = DB::connection('order')->select("select sellerid,(case MarketPlaceId
		when 'ATVPDKIKX0DER' then 'US'
		when 'A2EUQ1WTGCTBG2' then 'US'
		when 'A1AM78C64UM0Y8' then 'US'
		when 'A1F83G8C2ARO7P' then 'EU'
		when 'A1PA6795UKMFR9' then 'EU'
		when 'APJ6JRA9NG5V4' then 'EU'
		when 'A1RKKUPIHCS9HS' then 'EU'
		when 'A13V1IB3VIYZZH' then 'EU'
		when 'A1VC38T7YXB528' then 'JP'
		else 'US' End) as area from accounts GROUP BY sellerid,area");
		foreach($sellerids as $sellerid){
			$sellerid_area[$sellerid->sellerid] = $sellerid->area;
		}
		
		$stocks = DB::connection('order')->select('select sum(InStock) as stock,sum(Total-InStock) as transfer ,asin,sellerid from amazon_inventory_supply where InStock>0 or Total>0 group by asin,sellerid');
		foreach($stocks as $stock){
			if(!array_get($stock_t,$stock->asin.'.'.array_get($sellerid_area,$stock->sellerid).'.stock')) $stock_t[$stock->asin][array_get($sellerid_area,$stock->sellerid)]['stock']=0;
			if(!array_get($stock_t,$stock->asin.'.'.array_get($sellerid_area,$stock->sellerid).'.transfer')) $stock_t[$stock->asin][array_get($sellerid_area,$stock->sellerid)]['transfer']=0;
			$stock_t[$stock->asin][array_get($sellerid_area,$stock->sellerid)]['stock']+= intval($stock->stock);
			$stock_t[$stock->asin][array_get($sellerid_area,$stock->sellerid)]['transfer']+= intval($stock->transfer);
		}
		
		
		
		$stars = DB::select("select asin ,domain, average_score as avg_star,total_star_number as total_star  from star where create_at>='".date('Y-m-d',strtotime('-1 day'))."' or updated_at>='".date('Y-m-d',strtotime('-1 day'))."'");
		foreach($stars as $star){
			$data[$star->asin][array_get($site_marketplaceid,$star->domain)]['avg_star'] = round($star->avg_star,1);
			$data[$star->asin][array_get($site_marketplaceid,$star->domain)]['total_star'] = intval($star->total_star);
		}
		
		$asin_items = DB::select('select asin,site,item_no from asin where char_length(asin)=10 group by asin,site,item_no');
		foreach($asin_items as $item){
			$data[$item->asin][array_get($site_marketplaceid,$item->site)]['item_code'][]=$item->item_no;
		}
		//ÀûÈóÂÊ
		$dates = date('Ymd',strtotime('-30 day'));
		$profits = DB::select('select sum(sales_profits) as profit,sum(income) as income,item_code from asin_profits where date>=:dates group by item_code',['dates' => $dates]);
		foreach($profits as $profit){
			$sku_fbm_stock[$profit->item_code]['profit'] = round($profit->profit,2);
			$sku_fbm_stock[$profit->item_code]['income'] = round($profit->income,2);
		}
		
		
		$ads = DB::select('select sum(cost_base) as cost,item_code from asin_ads where date_start>=:dates group by item_code',['dates' => $dates]);
		foreach($ads as $ad){
			$sku_fbm_stock[$ad->item_code]['ads'] = round($ad->cost,2);
		}
		
		
		
		DB::table('seller_asins')->truncate();
		foreach($data as $asin=>$asind){
			foreach($asind as $marketplaceid=>$val){
				$item_code_arr = array_get($val,'item_code',[]);
				$item_name_arr = [];
				$get_profits=$pf=$in=$ad=$cost=$amount=$fbm_stock_total=0;
				foreach($item_code_arr as $i_c){
					$pf+=array_get($sku_fbm_stock,$i_c.'.profit',0);
					$in+=array_get($sku_fbm_stock,$i_c.'.income',0);
					$ad+=array_get($sku_fbm_stock,$i_c.'.ads',0);
					$cost = array_get($sku_fbm_stock,$i_c.'.cost',0);
					$amount+=array_get($sku_fbm_stock,$i_c.'.fbm_amount',0);
					$fbm_stock_total+=array_get($sku_fbm_stock,$i_c.'.fbm_stock',0);
					$item_name_arr[] = array_get($sku_fbm_stock,$i_c.'.item_name');
				}
				if($in<0) $in=1;
				if($in){
					$get_profits= round(($pf-$ad)/$in*100,2);
				}
				
				$stock_amount = (array_get($stock_t,$asin.'.'.array_get($marketplaceid_area,$marketplaceid).'.stock',0)+array_get($stock_t,$asin.'.'.array_get($marketplaceid_area,$marketplaceid).'.transfer',0))*$cost+$amount;
				$stock_total = 	array_get($stock_t,$asin.'.'.array_get($marketplaceid_area,$marketplaceid).'.stock',0)+array_get($stock_t,$asin.'.'.array_get($marketplaceid_area,$marketplaceid).'.transfer',0)+$fbm_stock_total;	
				$stock_keep = array_get($sales_t,$asin.'.'.array_get($marketplaceid_area,$marketplaceid).'.total_sales',0)?$stock_total/array_get($sales_t,$asin.'.'.array_get($marketplaceid_area,$marketplaceid).'.total_sales',0):$stock_total*100;
			
				$fba_stock_keep = array_get($sales_t,$asin.'.'.array_get($marketplaceid_area,$marketplaceid).'.total_sales',0)?($stock_total-$fbm_stock_total)/array_get($sales_t,$asin.'.'.array_get($marketplaceid_area,$marketplaceid).'.total_sales',0):($stock_total-$fbm_stock_total)*100;
				$positive_value=$negative_value=0;
				
				if($stock_amount>1000000){
					$positive_value+=10;
					$negative_value+=10;
				}elseif($stock_amount>500000){
					$positive_value+=8;
					$negative_value+=8;
				}elseif($stock_amount>100000){
					$positive_value+=5;
					$negative_value+=5;
				}elseif($stock_amount>10000){
					$positive_value+=3;
					$negative_value+=3;
				}elseif($stock_amount>=5000){
					$positive_value+=2;
					$negative_value+=2;
				}
				
				
				if($stock_keep>=200){
					$positive_value+=10;
					$negative_value+=10;
				}elseif($stock_amount>=100){
					$positive_value+=9;
					$negative_value+=9;
				}elseif($stock_amount>=60){
					$positive_value+=7;
					$negative_value+=7;
				}elseif($stock_amount>=30){
					$positive_value+=4;
					$negative_value+=4;
				}else{
					$positive_value+=1;
					$negative_value+=1;
				}
				
				$get_star_rating=round(array_get($val,'avg_star',0),1);
				if($get_star_rating==4.4){
					$positive_value+=2;
					$negative_value+=2;
				}elseif($get_star_rating==4.3){
					$positive_value+=6;
					$negative_value+=8;
				}elseif($get_star_rating==4.2){
					$positive_value+=8;
					$negative_value+=10;
				}elseif($get_star_rating==4.1){
					$positive_value+=4;
					$negative_value+=6;
				}elseif($get_star_rating==4.0){
					$positive_value+=2;
					$negative_value+=4;
				}elseif($get_star_rating==3.9){
					$positive_value+=1;
					$negative_value+=2;
				}else{
					$positive_value+=1;
					$negative_value+=1;
				}
				
				$total_star_count=intval(array_get($val,'total_star',0));
				
				if($total_star_count>=2000){
					
				}elseif($total_star_count>=1000){
					$positive_value+=1;
					$negative_value+=1;
				}elseif($total_star_count>=500){
					$positive_value+=2;
					$negative_value+=2;
				}elseif($total_star_count>=300){
					$positive_value+=3;
					$negative_value+=3;
				}elseif($total_star_count>=200){
					$positive_value+=4;
					$negative_value+=4;
				}elseif($total_star_count>=100){
					$positive_value+=5;
					$negative_value+=5;
				}elseif($total_star_count>=50){
					$positive_value+=6;
					$negative_value+=6;
				}elseif($total_star_count>=10){
					$positive_value+=7;
					$negative_value+=7;
				}elseif($total_star_count>=5){
					$positive_value+=8;
					$negative_value+=8;
				}elseif($total_star_count>=1){
					$positive_value+=10;
					$negative_value+=10;
				}elseif($total_star_count==0){
					$positive_value+=10;
				}
				
				
				DB::table('seller_asins')->insert(
					array(
						'asin'=>$asin,
						'marketplaceid'=>$marketplaceid,
						'site'=>'www.'.array_get(getSiteUrl(),$marketplaceid),
						'sales'=>array_get($val,'sales',0),
						'total_sales'=>array_get($sales_t,$asin.'.'.array_get($marketplaceid_area,$marketplaceid).'.total_sales',0),
						'stock'=>array_get($stock_t,$asin.'.'.array_get($marketplaceid_area,$marketplaceid).'.stock',0),
						'transfer'=>array_get($stock_t,$asin.'.'.array_get($marketplaceid_area,$marketplaceid).'.transfer',0),
						'item_code'=>serialize($item_code_arr),
						'item_name'=>serialize($item_name_arr),
						'cost'=>$cost,
						'fbm_stock'=>$fbm_stock_total,
						'fbm_amount'=>$amount,
						'total_star'=>array_get($val,'total_star',0),
						'avg_star'=>array_get($val,'avg_star',0),
						'profits'=>$get_profits,
						'fba_stock_keep'=>$fba_stock_keep,
						'stock_keep'=>$stock_keep,
						'stock_amount'=>$stock_amount,
						'positive_value'=>$positive_value,
						'negative_value'=>$negative_value,
						'updated_at'=>date('Y-m-d'),
					)
				);
			}
		}
		DB::update("update review set negative_value=IFNULL((select negative_value from seller_asins where seller_asins.asin=review.asin and seller_asins.site=review.site)+(case 
	when DATEDIFF(CURRENT_DATE(),date)<=1 then 10
	when DATEDIFF(CURRENT_DATE(),date)<=7 and DATEDIFF(CURRENT_DATE(),date)>1 then 9
	when DATEDIFF(CURRENT_DATE(),date)<=30 and DATEDIFF(CURRENT_DATE(),date)>7 then 8
	when DATEDIFF(CURRENT_DATE(),date)<=60 and DATEDIFF(CURRENT_DATE(),date)>30 then 7
	when DATEDIFF(CURRENT_DATE(),date)<=120 and DATEDIFF(CURRENT_DATE(),date)>60 then 6
	when DATEDIFF(CURRENT_DATE(),date)<=180 and DATEDIFF(CURRENT_DATE(),date)>120 then 5
	when DATEDIFF(CURRENT_DATE(),date)<=360 and DATEDIFF(CURRENT_DATE(),date)>180 then 4
	when DATEDIFF(CURRENT_DATE(),date)<=720 and DATEDIFF(CURRENT_DATE(),date)>360 then 3
	when DATEDIFF(CURRENT_DATE(),date)<=1000 and DATEDIFF(CURRENT_DATE(),date)>720 then 2
	when DATEDIFF(CURRENT_DATE(),date)>1000 then 1
	End),0) where date>'2015-01-01' and `status` =1");

		
    }

}
