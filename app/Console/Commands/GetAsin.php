<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Asin;
use App\Skusweekdetails;
use PDO;
use DB;
use Log;

class GetAsin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:asin {after} {before}';

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
		$exec_date = date('Y-m-d H:i:s',strtotime('-5 min'));
        $after =  $this->argument('after');
		$before =  $this->argument('before');
        if(!$after) $after = '3';
		
		$date_start=date('Ymd',strtotime('-'.$after.' days'));		
		$date_end=date('Ymd',strtotime('-'.$before.' days'));	
		$appkey = 'site0001';
		$appsecret= 'testsite0001';

		$array['date_start']='';
		$array['appid']= $appkey;
		$array['method']='getAsin';
		ksort($array);
		$authstr = "";
		foreach ($array as $k => $v) {
			$authstr = $authstr.$k.$v;
		}
		$authstr=$authstr.$appsecret;
		$sign = strtoupper(sha1($authstr));
		$res = file_get_contents('http://'.env("SAP_RFC").'/rfc_site.php?appid='.$appkey.'&method=getAsin&date_start=&date_end=&sign='.$sign);
		$result = json_decode($res,true);
		
		if(!array_get($result,'data')) die();
		$asinList = array_get($result,'data');

		foreach($asinList as $asin){

			
			$last_keywords=Skusweekdetails::where('asin',trim(array_get($asin,'ASIN','')))->where('marketplace_id',array_get(siteToMarketplaceid(),strtolower(array_get($asin,'SITE',''))))->whereNotNull('keywords')->orderBy('date','desc')->take(1)->value('keywords');
			
			$sku_reports = Skusweekdetails::where('asin',trim(array_get($asin,'ASIN','')))->where('marketplace_id',array_get(siteToMarketplaceid(),strtolower(array_get($asin,'SITE',''))))->orderBy('date','desc')->take(7)->get()->toArray();
			$sku_price=$sku_price_num=$sku_sales=$sku_sales_num=0;
			$sku_ranking=$sku_rating=$sku_review=$sku_strategy=NULL;
			foreach($sku_reports as $sku_report){
				if(!$sku_ranking) $sku_ranking=$sku_report['ranking'];
				if(!$sku_rating) $sku_rating=$sku_report['rating'];
				if(!$sku_review) $sku_review=$sku_report['review'];
				if(!$sku_strategy) $sku_strategy=$sku_report['strategy'];
				if(is_numeric($sku_report['price'])){
					$sku_price_num++;
					$sku_price+=$sku_report['price'];
				}
				if(is_numeric($sku_report['sales'])){
					$sku_sales_num++;
					$sku_sales+=$sku_report['sales'];
				}
			}
			$sku_price= ($sku_price_num)?round($sku_price/$sku_price_num,2):0;
			$sku_sales= ($sku_sales_num)?round($sku_sales/$sku_sales_num,2):0;
			$exists = DB::table('rating_asin')->where('asin', trim(array_get($asin,'ASIN')))->where('domain', 'www.'.trim(array_get($asin,'SITE')))->first();
			if(empty($exists)) {
				DB::table('rating_asin')->insert(
						array(
							'asin'=>trim(array_get($asin,'ASIN')),
							'domain'=>'www.'.trim(array_get($asin,'SITE'))
						)
				);
			}
			Asin::updateOrCreate(
			[
				'asin' => trim(array_get($asin,'ASIN','')),
				'site' => 'www.'.trim(array_get($asin,'SITE','')),
				'sellersku'=> trim(array_get($asin,'SELLER_SKU',''))],[
				'item_no' => trim(array_get($asin,'MATNR','')),
				'seller' => trim(array_get($asin,'SELLER','')),
				'item_group' => trim(array_get($asin,'MATKL','')),
				'status' => trim(array_get($asin,'ZSTATUS','')),
				'item_model' => trim(array_get($asin,'MODEL','')),
				'bg' => trim(array_get($asin,'ZBGROUP','')),
				'bu' => trim(array_get($asin,'ZBUNIT','')),
				'store' => trim(array_get($asin,'STORE','')),
				'brand' => trim(array_get($asin,'BRAND','')),
				'brand_line' => trim(array_get($asin,'WGBEZ','')),
				'sap_seller_id' => trim(array_get($asin,'VKGRP','')),
				'sap_site_id' => trim(array_get($asin,'VKBUR','')),
				'sap_store_id' => trim(array_get($asin,'KUNNR','')),
				'sap_warehouse_id' => trim(array_get($asin,'LGORT','')),
				'sap_factory_id' => trim(array_get($asin,'WERKS','')),
				'sap_shipment_id' => trim(array_get($asin,'SDABW','')),
				'item_status' => intval(array_get($asin,'MATNRZT',0)),
				'sku_ranking'=>$sku_ranking,
				'sku_rating'=>$sku_rating,
				'sku_review'=>$sku_review,
				'sku_price'=>$sku_price,
				'sku_sales'=>$sku_sales,
				'last_keywords'=>$last_keywords,
				'sku_strategy'=>$sku_strategy,
				'asin_last_update_date'=> date('Y-m-d H:i:s')
			]);
			
			if( array_get($asin,'ZSTATUS')=='A' || array_get($asin,'ZSTATUS')=='B'){
				$exists = DB::table('asin_seller_count')->where('asin', trim(array_get($asin,'ASIN')))->where('site', 'www.'.trim(array_get($asin,'SITE')))->first();
				if($exists) {
					DB::table('asin_seller_count')->where('asin', trim(array_get($asin,'ASIN')))->where('site', 'www.'.trim(array_get($asin,'SITE')))->update(array('updated_at'=>date('Y-m-d H:i:s'),'status'=>array_get($asin,'ZSTATUS'),'seller'=>array_get($asin,'SELLER')));
				}else{
					DB::table('asin_seller_count')->insert(
						array(
							'site' => 'www.'.trim(array_get($asin,'SITE')),
							'asin' => trim(array_get($asin,'ASIN')),
							'created_at'=>date('Y-m-d H:i:s'),
							'updated_at'=>date('Y-m-d H:i:s'),
							'status'=>array_get($asin,'ZSTATUS'),
							'seller'=>array_get($asin,'SELLER'),
						)
					);
				}
			}
			
			
    	}
		Asin::where('asin_last_update_date','<',$exec_date)->delete();
	}

}
