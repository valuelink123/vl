<?php
/*
 * 统计CRM模块的相关统计数据
 * CRM模块，总共涉及3个表client，client_info，client_order_info
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;

class StaClient extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'sta:client';

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

	/*
	 * 统计历史crm模块数据的times_ctg,times_rsg,times_negative_review,times_review
	 */
	function handle()
	{
		set_time_limit(0);
		//求参加了ctg客户的数据，然后同一客户进行统计
		$this->getTimesCtg();
		//参加了RSG客户的数据，求出参加RSG活动的次数
		$this->getTimesRsg();
		//获取留评相关数据信息，times_review为留评次数，times_negative_review为差评次数，存在ReviewTable并且Review星级为1-3级的为差评
		$this->getTimesReview();
	}

	/*
	 * 统计c客户参加ctg的次数
	 */
	function getTimesCtg()
	{
		$config = getCtgCashbackBogoConfig();
		$ctgData = array();
		foreach($config as $val){
			$sql = "select client_id as id,steps 
				FROM {$val['table']} as a 
				join client_info as b on a.email=b.email 
				where steps is not null";
			Log::Info($sql);
			$ctgData = array_merge($ctgData,$this->queryRows($sql));
		}
		$update = array();
		foreach($ctgData as $key=>$val){
			if(!empty($val['steps'])){
				$steps = json_decode($val['steps'],true);
				if(!empty($steps['commented']) && $steps['commented'] == 1){
					$update[$val['id']]['id'] = $val['id'];
					$update[$val['id']]['times_ctg'] = isset($update[$val['id']]['times_ctg']) ? $update[$val['id']]['times_ctg'] + 1 : 1;
				}
			}
		}
		Log::Info($update);
		return batchUpdate($update,'id','client');
	}

	/*
	 *参加了RSG客户的数据，求出参加RSG活动的次数
	 */
	function getTimesRsg()
	{
		$update = array();
		$sql = "select client_id as id  
				FROM rsg_requests as a 
				join client_info as b on a.customer_email=b.email 
				where step = 9";
		Log::Info($sql);
		$rsgData = $this->queryRows($sql);
		foreach($rsgData as $key=>$val){
			$update[$val['id']]['id'] = $val['id'];
			$update[$val['id']]['times_rsg'] = isset($update[$val['id']]['times_rsg']) ? $update[$val['id']]['times_rsg'] + 1 : 1;
		}
		Log::Info($update);
		return batchUpdate($update,'id','client');
	}

	/*
	 * 获取留评相关数据信息，times_review为留评次数，times_negative_review为差评次数，存在ReviewTable并且Review星级为1-3级的为差评
	 */
	function getTimesReview()
	{
		$update = array();
		$sql = "select client_id as id,rating 
				FROM review as a 
				join client_info as b on a.buyer_email=b.email";
		Log::Info($sql);
		$reviewData = $this->queryRows($sql);
		foreach($reviewData as $key=>$val){
			$update[$val['id']]['id'] = $val['id'];
			$update[$val['id']]['times_review'] = isset($update[$val['id']]['times_review']) ? $update[$val['id']]['times_review'] + 1 : 1;//留评次数
			$add_negative_review = 0;
			if($val['rating']<=3){
				$add_negative_review = 1;//差评次数
			}
			$update[$val['id']]['times_negative_review'] = isset($update[$val['id']]['times_negative_review']) ? $update[$val['id']]['times_negative_review'] + $add_negative_review : $add_negative_review;//差评次数
		}
		Log::Info($update);
		return batchUpdate($update,'id','client');
	}

}



