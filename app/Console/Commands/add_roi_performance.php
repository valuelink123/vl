<?php
/* Date: 2019.9.21
 * Author: wulanfnag
 * 
 *
 * */
namespace App\Console\Commands;

use App\AsinSalesPlan;
use Illuminate\Console\Command;
use DB;
use Log;

class AddRoiPerformance extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'add:roi_performance';

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

	//添加库存预警数据
	function handle()
	{
		set_time_limit(0);
		Log::Info('add:roi_performance Start...');

		$sql = "SELECT roi.sku,roi.id as roi_id
				from roi 
				left join roi_performance on roi_performance.roi_id=roi.id
				where sku is not null and roi_id is null";

		$_data = DB::connection('amazon')->select($sql);
		$typeConfig = array('income', 'cost', 'commission', 'operate_fee', 'storage_fee', 'capital_occupy_cost', 'economic_benefit', 'promo');
		foreach($_data as $key=>$val){
			foreach($typeConfig as $type){
				$performanceData[] = array(
					'roi_id' => $val->roi_id,
					'type' => $type,
				);
			}
		}
		DB::connection('amazon')->table('roi_performance')->insert($performanceData);

		Log::Info('add:roi_performance end...');

	}


}



