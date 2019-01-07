<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Kunnr;
use PDO;
use DB;
use Log;

class GetShoudafang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:kunnr {after} {before}';

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
		$array['method']='getKunnr';
		ksort($array);
		$authstr = "";
		foreach ($array as $k => $v) {
			$authstr = $authstr.$k.$v;
		}
		$authstr=$authstr.$appsecret;
		$sign = strtoupper(sha1($authstr));
		$res = file_get_contents('http://116.6.105.153:18003/rfc_site.php?appid='.$appkey.'&method='.$array['method'].'&date_start='.$date_start.'&date_end='.$date_end.'&sign='.$sign);
		$result = json_decode($res,true);
		
		if(!array_get($result,'data')) die();
		$asinList = array_get($result,'data');

		foreach($asinList as $asin){

			if(array_get($asin,'ZDELETE')=='X'){
				Kunnr::where('seller_id', trim(array_get($asin,'SELLERID')))->where('site', trim(array_get($asin,'VKBUR')))->delete();
				continue;
			} 
			Kunnr::updateOrCreate(
			[
				'seller_id' => trim(array_get($asin,'SELLERID','')),
				'site' => trim(array_get($asin,'VKBUR',''))
				],[
				'kunnr' => trim(array_get($asin,'KUNNR','')),
				'date'=> date('Y-m-d H:i:s')
			]);
			
    	}
	}

}
