<?php
/* Date: 2019.9.21
 * Author: wulanfnag
 * 22周销售计划中，还没有填写销售计划的时候，插件提醒销售去添加计划
 *
 * */
namespace App\Console\Commands;

use App\AsinSalesPlan;
use Illuminate\Console\Command;
use DB;
use Log;

class AddSalesRemind extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'add:sales_remind';

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
		$date = date('Y-m-d');
		Log::Info('Add sales_remind Start...');
		DB::connection()->enableQueryLog(); // 开启查询日志
		DB::select('truncate remind_msg');

		$date_1w = date('Y-m-d',strtotime($date.'+1 weeks sunday'));//这周末的日期
		$date_22w = date('Y-m-d',strtotime($date.' +22 weeks sunday'));//第22周的周天
		$sql = "SELECT a.asin as asin,a.marketplace_id as marketplace_id,a.sap_seller_id as sap_seller_id 
				from (
						select asin,marketplace_id,any_value(sku) as sku,any_value(sku_status) as sku_status,any_value(sap_seller_id) as sap_seller_id 
						from sap_asin_match_sku where sku_status<6 group by asin,marketplace_id
				) as a
				left join asins as b on a.asin=b.asin and a.marketplace_id=b.marketplaceid
				left join (
						select sku,sum(quantity) as sz_sellable from sap_sku_sites where left(sap_factory_code,2)='HK' group by sku
						) as d on a.sku=d.sku
				where (sku_status>0 or (afn_sellable+afn_reserved+mfn_sellable+sz_sellable)>0)";

		$_data = DB::connection('amazon')->select($sql);
		$remindData = array();
		$data = array();
		$useridSapid = getUseridSapid();
		foreach($_data as $tk=>$tv){
			if(in_array($tv->sap_seller_id,$useridSapid)){
				$data[$tv->asin.'_'.$tv->marketplace_id] = array(
					'asin' => $tv->asin,
					'marketplace_id' => $tv->marketplace_id,
					'sap_seller_id' => $tv->sap_seller_id,
					'user_id' => in_array($tv->sap_seller_id,$useridSapid) ?array_search($tv->sap_seller_id, $useridSapid) : 0,
				);
			}
		}

		foreach($data as $key=>$val){
			$asin_plans = AsinSalesPlan::SelectRaw('week_date')->where('asin',$val['asin'])->where('marketplace_id',$val['marketplace_id'])->where('week_date','>=',$date_1w)->where('week_date','<=',$date_22w)->groupBy(['week_date'])->get()->keyBy('week_date')->toArray();//获取销售预测数据,检测是否填写数据
			for($i=1;$i<=22;$i++) {
				$date_w = date('Y-m-d', strtotime($date . ' +' . $i . ' weeks sunday'));
				if(!isset($asin_plans[$date_w])){//一个user只提醒一次
					$remindData[$val['user_id']] = array(
						'user_id' => $val['user_id'],
						'status' => 0,
					);
				}
			}

		}
		if($remindData){
			batchInsert('remind_msg',array_values($remindData));//数据插入提醒消息表
		}
		Log::Info('Execution script end...');

	}


}



