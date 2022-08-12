<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use App\Exception;
use App\Asin;
use App\Models\SellerAccountsStatusRecord;
use PDO;
use DB;
use Log;
class AutoReplacement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:replacement';

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
		
		$autoDatas = Exception::where('type',2)->where('process_status','confirmed')->whereNotNull('service_system_id')->where('process_date','>',date('Y-m-d H:i:s',strtotime('-2 days')))->where('process_date','<',date('Y-m-d H:i:s',strtotime('-1 days')))->get();
		foreach($autoDatas as $autoData){
			try{
				$shipData = unserialize($autoData->replacement);
				$countryCode = array_get($shipData,'countrycode');
				$products = array_get($shipData,'products');
				$newProducts = [];
				foreach($products as $product){
					$item_code = array_get($product,'item_code');
					$shipInfo = $this->getShipmentInfo($item_code, $countryCode);
					if(!empty($shipInfo)){
						$shipInfo = $shipInfo[0];
						$product['seller_id'] = $shipInfo->seller_id;
						$product['seller_sku'] = $shipInfo->seller_sku;
						$product['shipfrom'] = $countryCode;
						$product['note'] = $shipInfo->seller_name.' | '.$shipInfo->seller_sku;
						$newProducts[] = $product;
					}else{
						$newProducts = [];
						break;
					}
				}
				if(!empty($newProducts)){
					$shipData['products'] = $newProducts;
					$autoData->replacement = serialize($shipData);
					$autoData->process_status = 'auto done';
					$autoData->process_content = 'auto done over 24 hours';
					$autoData->process_user_id = $autoData->auto_create_mcf = $autoData->auto_create_sap = 1;
					$autoData->process_date = $autoData->last_auto_create_mcf_date = $autoData->last_auto_create_sap_date = date('Y-m-d H:i:s');
					$autoData->save();
					$params = json_encode($autoData);
					$return  = curl_request('http://www.onecustomerme.com/api/exception/webhook',['params'=>$params]);
					$return = json_decode($return,true);
					if(array_get($return,'result')!='ok'){
						throw new \Exception("Data synchronization failed, please try again");
					}
				}
			} catch (\Exception $e) {
				Log::Info($e->getMessage());
				continue;
			}
		}
	}
	
	public function getShipmentInfo($item_code, $countryCode){

        $accountStatusModel = new SellerAccountsStatusRecord();
		$accountInfo = $accountStatusModel->getEnableAccountInfo();
		$accountInfo = array_keys($accountInfo);
		$accountInfo = implode("','",$accountInfo);
		$accountInfo = "'".$accountInfo."'";

        $rows = DB::select(
            "SELECT
                item_code,
                seller_id,
                seller_name,
                seller_sku,
                item_name,
                fba_stock AS stock
            FROM
                fba_stock
            LEFT JOIN fbm_stock USING (item_code)
            WHERE
                item_code = '{$item_code}' 
			AND account_status = 0 and fba_stock>0
			and CONCAT(site,'_',seller_id) in({$accountInfo})
			order by fba_stock desc limit 1"
        );
        return $rows;
	}
}
