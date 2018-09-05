<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Asin;
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
		$array['method']='getAsin';
		ksort($array);
		$authstr = "";
		foreach ($array as $k => $v) {
			$authstr = $authstr.$k.$v;
		}
		$authstr=$authstr.$appsecret;
		$sign = strtoupper(sha1($authstr));
		
		$res = file_get_contents('http://116.6.105.153:18003/rfc_site.php?appid='.$appkey.'&method=getAsin&date_start='.$date_start.'&date_end='.$date_end.'&sign='.$sign);
		$result = json_decode($res,true);
		
		if(!array_get($result,'data')) die();
		$asinList = array_get($result,'data');

		foreach($asinList as $asin){
			//if(array_get($asin,'ASIN') == 'B074NXJWN2') print_r($asin);
			unset($exists);
			if(array_get($asin,'ZDELETE')=='X'){
				Asin::where('asin', trim(array_get($asin,'ASIN')))->where('site', 'www.'.trim(array_get($asin,'SITE')))->where('sellersku', trim(array_get($asin,'SELLER_SKU')))->delete();
				DB::table('asin_seller_count')->where('asin', trim(array_get($asin,'ASIN')))->where('site', 'www.'.trim(array_get($asin,'SITE')))->update(array('updated_at'=>date('Y-m-d H:i:s'),'status'=>'X'));
				continue;
			} 
			
			$exists = Asin::where('asin', trim(array_get($asin,'ASIN')))->where('site', 'www.'.trim(array_get($asin,'SITE')))->where('sellersku', trim(array_get($asin,'SELLER_SKU')))->first();
			if(!$exists) {
				
				//if(array_get($asin,'ASIN') == 'B074NXJWN2') echo '1';
				$asinadd = new Asin;
				$asinadd->asin = trim(array_get($asin,'ASIN'));
				$asinadd->site = 'www.'.trim(array_get($asin,'SITE'));
				$asinadd->sellersku = trim(array_get($asin,'SELLER_SKU'));
				$asinadd->item_no = array_get($asin,'MATNR');
				$asinadd->seller = array_get($asin,'SELLER');
				$asinadd->item_group = array_get($asin,'MATKL');
				$asinadd->status = array_get($asin,'ZSTATUS');
				$asinadd->item_model = array_get($asin,'MODEL');
				$asinadd->bg = array_get($asin,'ZBGROUP');
				$asinadd->bu = array_get($asin,'ZBUNIT');
				$asinadd->store = array_get($asin,'STORE');
				$result = $asinadd->save();
				//if(array_get($asin,'ASIN') == 'B074NXJWN2') print_r($asinadd);
				unset($asinadd);
				//if(array_get($asin,'ASIN') == 'B074NXJWN2') print_r($result);
				
				
			}else{
				
				//if(array_get($asin,'ASIN') == 'B074NXJWN2') echo '2';
				$exists->item_no = array_get($asin,'MATNR');
				$exists->seller = array_get($asin,'SELLER');
				$exists->item_group = array_get($asin,'MATKL');
				$exists->status = array_get($asin,'ZSTATUS');
				$exists->item_model = array_get($asin,'MODEL');
				$exists->bg = array_get($asin,'ZBGROUP');
				$exists->bu = array_get($asin,'ZBUNIT');
				$exists->store = array_get($asin,'STORE');
				$exists->save();
			
			}
			
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
	}

}
