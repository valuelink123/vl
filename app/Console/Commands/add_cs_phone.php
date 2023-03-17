<?php
/*
 * 官网库wp_gf_entry_meta表中meta_key=1.3为姓名，等于2位邮箱，等于3为亚马逊邓丹id(需配置)
 * 此腳本是一次性腳本，添加历史non_ctg数据，此数据表示参加激活质保但是没有参加CTG活动的用户信息
 * 在激活质保的用户中用订单号判断是否存在于ctg表中，一个订单号可以在多个官网上激活质保，但是只能参加一次ctg活动
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;
use App\Classes\SapRfcRequest;

class CsPhone extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'add:cs_phone';

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

	public function __destruct()
	{

	}

	//添加历史nonctg数据
	function handle()
	{
		set_time_limit(0);
		$today = date('Y-m-d');
		echo 'Execution add_cs_phone.php script start time:'.$today."\n";
		set_time_limit(0);
		$maxDate = DB::table('phone')->max('date');
		$data = DB::connection('cs')->table('phone')->select("seller_email", "buyer_email",'content','phone','seller_id','amazon_order_id','remark','etype','sku','asin','item_no','epoint','date','user_id','linkage1','linkage2','linkage3','linkage4','linkage5')->where('date','>',$maxDate)->get();
		$data = json_decode(json_encode($data),true);
		if($data){
			$data_arr = array_chunk($data,500);
			foreach($data_arr as $value){
				DB::table('phone')->insert($value);
			}
		}
		echo 'Execution script end';
	}
}



