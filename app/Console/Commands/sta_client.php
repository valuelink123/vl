<?php
/*
 * 统计CRM模块的相关统计数据
 * CRM模块，总共涉及3个表client，client_info，client_order_info
 * times_ctg，客户在CTG列表内，则记为1
 * times_rsg，客户在RSG列表内，则记为1
 * times_negative_review为差评次数,review表的rating为1-3则为差评
 * times_positive_review为好评次数，CTG留评状态显示为是+RSG Complete里star rating 为4-5星的数据
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
	 * 统计历史crm模块数据的times_ctg,times_rsg,times_negative_review,times_positive_review
	 */
	function handle()
	{
		set_time_limit(0);
		//求参加了ctg客户的数据，然后同一客户进行统计
		$this->getTimesCtgRsgPositive();
		//获取留评相关数据信息，times_negative_review为差评次数，times_positive_review为好评次数，存在ReviewTable并且Review星级为1-3级的为差评
		$this->getTimesReview();
	}

	/*
	 * 统计crm模块客户参加ctg,rsg,好评的次数
	 * CTG客户在CTG列表内，则记为1
	 * times_rsg，客户在RSG列表内，则记为1
	 * times_positive_review为好评次数，CTG留评状态显示为是+RSG Complete里star rating 为4-5星的数据
	 */
	function getTimesCtgRsgPositive()
	{
		$ctgData = $this->getCtgData();
		$updateCtg = $updateRsg = $updatePositive = array();
		foreach($ctgData as $key=>$val){
			//times_ctg次数
			$updateCtg[$val['id']]['id'] = $val['id'];
			$updateCtg[$val['id']]['times_ctg'] = isset($updateCtg[$val['id']]['times_ctg']) ? $updateCtg[$val['id']]['times_ctg'] + 1 : 1;
			//好评次数
			if(!empty($val['steps'])){
				$steps = json_decode($val['steps'],true);
				if(!empty($steps['commented']) && $steps['commented'] == 1){
					$updatePositive[$val['id']]['id'] = $val['id'];
					$updatePositive[$val['id']]['times_positive_review'] = isset($updatePositive[$val['id']]['times_positive_review']) ? $updatePositive[$val['id']]['times_positive_review'] + 1 : 1;
				}
			}
		}
		//获取rsg数据
		$rsgData = $this->getRsgData();
		foreach($rsgData as $key=>$val){
			//rsg次数
			$updateRsg[$val['id']]['id'] = $val['id'];
			$updateRsg[$val['id']]['times_rsg'] = isset($updateRsg[$val['id']]['times_rsg']) ? $updateRsg[$val['id']]['times_rsg'] + 1 : 1;

			//好评次数
			if($val['star_rating']>=4){
				$updatePositive[$val['id']]['id'] = $val['id'];
				$updatePositive[$val['id']]['times_positive_review'] = isset($updatePositive[$val['id']]['times_positive_review']) ? $updatePositive[$val['id']]['times_positive_review'] + 1 : 1;
			}
		}
		Log::Info($updateCtg,$updateRsg,$updatePositive);
		//更改数据
		if($updateCtg){
			batchUpdate($updateCtg,'id','client');
		}
		if($updateRsg){
			batchUpdate($updateRsg,'id','client');
		}
		if($updatePositive){
			batchUpdate($updatePositive,'id','client');
		}

		return true;
	}

	/*
	 * 获取留评相关数据信息，times_review为留评次数，times_negative_review为差评次数，存在ReviewTable并且Review星级为1-3级的为差评
	 */
	function getTimesReview()
	{
		$update = array();
		//差评次数
		$sql = "select client_id as id,rating 
				FROM review as a 
				join client_info as b on a.buyer_email=b.email 
				where rating <= 3";
		Log::Info($sql);
		$reviewData = $this->queryRows($sql);
		foreach($reviewData as $key=>$val){
			$update[$val['id']]['id'] = $val['id'];
			$update[$val['id']]['times_negative_review'] = isset($update[$val['id']]['times_negative_review']) ? $update[$val['id']]['times_negative_review'] + 1 : 1;//差评次数
		}

		Log::Info($update);
		return batchUpdate($update,'id','client');
	}

	//获取ctg表相关数据
	function getCtgData()
	{
		$config = getCtgCashbackBogoConfig();
		$ctgData = array();
		foreach($config as $val){
			$sql = "select client_id as id,steps   
				FROM {$val['table']} as a 
				join client_info as b on a.email=b.email ";
			Log::Info($sql);
			$ctgData = array_merge($ctgData,$this->queryRows($sql));
		}
		return $ctgData;
	}

	//获取rsg表相关数据
	function getRsgData()
	{
		$sql = "select client_id as id,star_rating  
				FROM rsg_requests as a 
				join client_info as b on a.customer_email=b.email 
				where step = 9";
		Log::Info($sql);
		$rsgData = $this->queryRows($sql);
		return $rsgData;
	}

}



