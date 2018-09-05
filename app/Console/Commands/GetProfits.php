<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Asinprofits;
use PDO;
use DB;
use Log;

class GetProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:profits {after} {before}';

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
		$array['method']='getProfits';
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
		Asinprofits::where('date','<',date('Ym',strtotime('-1 month')).'01')->delete();
		foreach($asinList as $asin){
			unset($exists);
			$exists = Asinprofits::where('item_code', trim(array_get($asin,'VERSN')))->where('date', trim(array_get($asin,'BUDAT')))->where('seller_code', trim(array_get($asin,'WW004')))->first();
			if(!$exists) {
				$exists = new Asinprofits;
				$exists->item_code = trim(array_get($asin,'VERSN'));
				$exists->date = trim(array_get($asin,'BUDAT'));
				$exists->seller_code = trim(array_get($asin,'WW004'));
			}
			$exists->seller_name = trim(array_get($asin,'BEZEI'));
			$exists->item_name  = trim(array_get($asin,'NAME1'));
			$exists->sold_qty = getSapNumber(trim(array_get($asin,'VV001')),2);
			$exists->trans_qty = getSapNumber(trim(array_get($asin,'VV002')),2);
			$exists->buyer_shipping_fee = getSapNumber(trim(array_get($asin,'VV003')),2);
			$exists->buyer_shipping_fee_add = getSapNumber(trim(array_get($asin,'VV004')),2);
			$exists->insurance_fee = getSapNumber(trim(array_get($asin,'VV005')),2);
			$exists->shipping_insurance_fee = getSapNumber(trim(array_get($asin,'VV006')),2);
			$exists->income = getSapNumber(trim(array_get($asin,'VSRHJ')),2);
			$exists->sell_tax = getSapNumber(trim(array_get($asin,'VV007')),2);
			$exists->cost = getSapNumber(trim(array_get($asin,'VV009')),2);
			$exists->cost_total = getSapNumber(trim(array_get($asin,'VCBHJ')),2);
			$exists->platfrom_fee = getSapNumber(trim(array_get($asin,'VV010')),2);
			$exists->submit_fee = getSapNumber(trim(array_get($asin,'VV011')),2);
			$exists->trans_fee = getSapNumber(trim(array_get($asin,'VV012')),2);
			$exists->exchange_fee = getSapNumber(trim(array_get($asin,'VV013')),2);
			$exists->shipping_fee = getSapNumber(trim(array_get($asin,'VV014')),2);
			$exists->warehouse_operation_fee = getSapNumber(trim(array_get($asin,'VV015')),2);
			$exists->depreciation_fee = getSapNumber(trim(array_get($asin,'VV016')),2);
			$exists->fba_fee = getSapNumber(trim(array_get($asin,'VV018')),2);
			$exists->return_depreciation_fee = getSapNumber(trim(array_get($asin,'VV023')),2);
			$exists->promotion_fee = getSapNumber(trim(array_get($asin,'VV024')),2);
			$exists->sales_discounts = getSapNumber(trim(array_get($asin,'VFYHJ')),2);
			$exists->sales_profits = getSapNumber(trim(array_get($asin,'VVVVV')),2);
			$exists->actual_cost = getSapNumber(trim(array_get($asin,'VV028')),2);
			$exists->abnormal_bill = getSapNumber(trim(array_get($asin,'VV017')),2);
			$exists->item_group = trim(array_get($asin,'MATKL'));
			$exists->item_group_des = trim(array_get($asin,'WGBEZ'));
			$exists->updated_at = date('Y-m-d H:i:s');
			$exists->save();
    	}
	}

}
