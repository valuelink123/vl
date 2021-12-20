<?php
/* Date: 2021.12.20
 * Author: wulanfnag
 * 添加asins数据
 * 每天跑一次此处理
 * */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;
use App\Models\Asins;

class AddAsins extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'add:asins';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	protected $date = '';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

	}

	public function __destruct()
	{

	}

	//添加asins数据
	function handle()
	{
		set_time_limit(0);
		Log::Info('Add asins Start...');
		DB::connection()->enableQueryLog(); // 开启查询日志
		$users = getUsers('sap_seller');
		$users = json_decode(json_encode($users),true);
		$sap_seller_id_str = implode(",",array_keys($users));
		$sql = "SELECT DISTINCT asin,seller_id,marketplace_id,sap_seller_id,seller_accounts.mws_seller_id 
				FROM sap_asin_match_sku 
				LEFT JOIN seller_accounts ON sap_asin_match_sku.seller_id = seller_accounts.mws_seller_id
				WHERE LENGTH(ASIN)=10 
				AND LENGTH(SELLER_ID)>8 
				AND ASIN NOT IN ('0123456789','1234567890','1234567890','AAAAAAAAAA','ABCDEFGHIJ','ABCDEFGHIJ') 
				AND sap_seller_id IN (".$sap_seller_id_str.")
				AND seller_accounts.deleted_at IS null 
				AND seller_accounts.mws_seller_id IS NOT NULL";
		$data = DB::connection('amazon')->select($sql);
		$insertData = array();
		foreach($data as $key=>$val){
			$insertData[] = array(
				'asin'=>$val->asin,
				'marketplace'=>$val->marketplace_id,
				'create_at'=>date('Y-m-d H:i:s'),
				'update_at'=>date('Y-m-d H:i:s'),
			);
		}
		if($insertData){
			Asins::insertOnDuplicateWithDeadlockCatching($insertData, ['update_at']);
		}
		Log::Info('Execution script end...');
	}
}



