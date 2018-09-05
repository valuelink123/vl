<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Asinads;
use PDO;
use DB;
use Log;

class GetAds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ads {after} {before}';

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
		
        $after =  $this->argument('after');
		$before =  $this->argument('before');
        if(!$after) $after = '3';
		
		$date_start=date('Ymd',strtotime('-'.$after.' days'));		
		$date_end=date('Ymd',strtotime('-'.$before.' days'));	
		$appkey = 'site0001';
		$appsecret= 'testsite0001';
		//$date_start=date('Ymd',strtotime('-1000 days'));
		//$date_end=date('Ymd');
		$array['date_start']=$date_start;
		$array['appid']= $appkey;
		$array['method']='getAdFee';
		ksort($array);
		$authstr = "";
		foreach ($array as $k => $v) {
			$authstr = $authstr.$k.$v;
		}
		$authstr=$authstr.$appsecret;
		$sign = strtoupper(sha1($authstr));
		
		$res = file_get_contents('http://116.6.105.153:18003/rfc_site.php?appid='.$appkey.'&method='.$array['method'].'&date_start='.$date_start.'&date_end='.$date_end.'&sign='.$sign);
		//print_r($res);
		$result = json_decode($res,true);

		if(!array_get($result,'data')) die();
		$asinList = array_get($result,'data');
		Asinads::where('date_start','<',date('Ym',strtotime('-1 month')).'01')->delete();
		foreach($asinList as $asin){
			unset($exists);
			$exists = Asinads::where('date_start', trim(array_get($asin,'ZSDATE')))
			->where('date_end', trim(array_get($asin,'ZEDATE')))
			->where('campaign_name', trim(array_get($asin,'ZCN')))
			->where('sku', trim(array_get($asin,'ZSKU')))
			->where('site', trim(array_get($asin,'ZSITE')))
			->where('seller_code', trim(array_get($asin,'ZSALES')))
			->where('fee_type', trim(array_get($asin,'ZFYLX')))
			->where('item_code', trim(array_get($asin,'MATNR')))
			->first();
			if(!$exists) {
				$exists = new Asinads;
				$exists->date_start = trim(array_get($asin,'ZSDATE'));
				$exists->date_end = trim(array_get($asin,'ZEDATE'));
				$exists->campaign_name = trim(array_get($asin,'ZCN'));
				$exists->sku = trim(array_get($asin,'ZSKU'));
				$exists->site = trim(array_get($asin,'ZSITE'));
				$exists->seller_code = trim(array_get($asin,'ZSALES'));
				$exists->fee_type = trim(array_get($asin,'ZFYLX'));
				$exists->item_code = trim(array_get($asin,'MATNR'));
			}
			$exists->cost = getSapNumber(trim(array_get($asin,'ZCOS1')),2);
			$exists->cost_base  = getSapNumber(trim(array_get($asin,'ZCOS2')),2);
			$exists->sales = getSapNumber(trim(array_get($asin,'ZSALE1')),2);
			$exists->sales_base = getSapNumber(trim(array_get($asin,'ZSALE2')),2);
			$exists->profit = getSapNumber(trim(array_get($asin,'ZPRO')),2);
			$exists->income = getSapNumber(trim(array_get($asin,'ZPROFIT')),2);
			$exists->roi = getSapNumber(trim(array_get($asin,'ZROI')),4);
			$exists->acos = getSapNumber(trim(array_get($asin,'ZACOS')),4);
			$exists->exchange_rate = getSapNumber(trim(array_get($asin,'ZHL')),2);
			$exists->currency = trim(array_get($asin,'ZCURR1'));
			$exists->currency_base = trim(array_get($asin,'ZCURR2'));
			$exists->user_name = trim(array_get($asin,'ZNAME'));
			$exists->delete_tag = trim(array_get($asin,'ZDEL'));
			$exists->date = trim(array_get($asin,'ZDATE'));
			$exists->time = trim(array_get($asin,'ZTIME'));
			$exists->updated_at = date('Y-m-d H:i:s');
			$exists->save();
    	}
	}

}
