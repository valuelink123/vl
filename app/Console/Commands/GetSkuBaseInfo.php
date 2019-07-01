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
		//die('123');
		$skus_data=Asin::groupBy('item_no')->get(['item_no'])->toArray();

		$sap = new SapRfcRequest();
		foreach($skus_data as $sku_data){
			print_r($sku_data['item_no']);
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
