<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use App\SkusBase;
use App\SkusSiteBase;
use App\Asin;
use PDO;
use DB;
use Log;
use App\Classes\SapRfcRequest;
class GetSkuBaseInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:skubaseinfo';

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
		$skus_data=Asin::groupBy('item_no')->get(['item_no'])->toArray();

		$sap = new SapRfcRequest();
		foreach($skus_data as $sku_data){
        	$result = $sap->getSkuBaseInfo(['sku' => $sku_data['item_no']]);
			$O_TAB1 = array_get($result,'O_TAB1',[]);
			$O_TAB2 = array_get($result,'O_TAB2',[]);
			
			foreach($O_TAB1 as $otab1){	
				$MATNR= $otab1['MATNR'];
				array_shift($otab1);
				SkusBase::updateOrCreate(
				[
					'MATNR' => $MATNR
				],
				$otab1);
			
			}
			foreach($O_TAB2 as $otab2){
				$MATNR= $otab2['MATNR'];
				$VKBUR= $otab2['VKBUR'];
				array_shift($otab2);
				array_shift($otab2);
				$res = $sap->getStockAge(['sku' => $MATNR,'site' => $VKBUR]);
				$otab2['ZCRATIO'] = array_get($res,'1.ZCRATIO',NULL);
				$otab2['FBAPRICE'] = array_get($res,'1.FBAPRICE',NULL);
				$otab2['YCL'] = array_get($res,'1.YCL',NULL);
				$otab2['RATE'] = array_get($res,'1.RATE',NULL);
				
				$res = $sap->getTureSales(['sku' => $MATNR,'site' => $VKBUR,'month' => date('Ym',strtotime('-1 month'))]);
					
				$otab2['PRICE1'] = array_get($res,'1.T',NULL);
				$otab2['YWLRL1'] = array_get($res,'1.T',NULL);
				$otab2['YXFYL1'] = array_get($res,'1.T',NULL);
				$otab2['YWJLL1'] = array_get($res,'1.T',NULL);
				$otab2['XL1'] = array_get($res,'1.VV001',NULL);
				$otab2['XSE1'] = array_get($res,'1.VSRHJ',NULL);



				$res = $sap->getTureSales(['sku' => $MATNR,'site' => $VKBUR,'month' => date('Ym',strtotime('-2 month'))]);
				
				$otab2['PRICE2'] = array_get($res,'1.T',NULL);
				$otab2['YWLRL2'] = array_get($res,'1.T',NULL);
				$otab2['YXFYL2'] = array_get($res,'1.T',NULL);
				$otab2['YWJLL2'] = array_get($res,'1.T',NULL);
				$otab2['XL2'] = array_get($res,'1.VV001',NULL);
				$otab2['XSE2'] = array_get($res,'1.VSRHJ',NULL);
				
				$res = $sap->getTureSales(['sku' => $MATNR,'site' => $VKBUR,'month' => date('Ym',strtotime('-3 month'))]);
				
				
				$otab2['PRICE3'] = array_get($res,'1.T',NULL);
				$otab2['YWLRL3'] = array_get($res,'1.T',NULL);
				$otab2['YXFYL3'] = array_get($res,'1.T',NULL);
				$otab2['YWJLL3'] = array_get($res,'1.T',NULL);
				$otab2['XL3'] = array_get($res,'1.VV001',NULL);
				$otab2['XSE3'] = array_get($res,'1.VSRHJ',NULL);
				
				SkusSiteBase::updateOrCreate(
				[
					'MATNR' => $MATNR,
					'VKBUR' => $VKBUR
				],
				$otab2);
			}

		}

		
	}

}
