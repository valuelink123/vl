<?php
/*
 * 统计CRM模块的相关统计数据
 * CRM模块，总共涉及3个表client，client_info，client_order_info
 * times_ctg，客户在CTG列表内，则记为1
 * times_rsg，客户在RSG列表内，则记为1
 * times_negative_review为差评次数,review表的rating为1-3则为差评
 * times_positive_review为好评次数，CTG留评状态显示为是+RSG Complete里star rating 为4-5星的数据
 *
 *
 * 更新client表的rsg_status字段（sg资格判断，0，Available 绿色，1，Unavailable红色）和rsg_status_explain字段（如若rsg资格判断为1的时候，因为什么原因导致为红色）
 * 1，标签为黑名单、客户账号留评被限制的客户
 * 2，有过已付款未购买情况的客户
 * 3，留差评客户
 * 4，最近30天有参与4次RSG
 * 5，留评率低于90%的客户
 * 6，上个活动不是Completed状态
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
		$sql = "update client set rsg_status = 0,rsg_status_explain = 0,times_ctg = 0,times_rsg = 0,times_sg = 0,times_negative_review = 0,times_positive_review = 0,updated_at = '".date('Y-m-d H:i:s')."'";
		DB::select($sql);

		set_time_limit(0);
		//求参加了ctg客户的数据，然后同一客户进行统计
		$this->getTimesCtgRsgPositive();
		//获取留评相关数据信息，times_negative_review为差评次数，times_positive_review为好评次数，存在ReviewTable并且Review星级为1-3级的为差评
		$this->getTimesReview();
		//更新client表的rsg_status字段（sg资格判断，0，Availiable 绿色，1，Unaviliable红色）和rsg_status_explain字段（如若rsg资格判断为1的时候，因为什么原因导致为红色）
		$this->updateRsgStatus();
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
		$updateCtg = $updateRsg = $updatePositive = $updateSg = array();
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
					//sg数量（CTG中留好评的数量）
					$updateSg[$val['id']]['id'] = $val['id'];
					$updateSg[$val['id']]['times_sg'] = isset($updateSg[$val['id']]['times_sg']) ? $updateSg[$val['id']]['times_sg'] + 1 : 1;
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
		// Log::Info($updateCtg,$updateRsg,$updatePositive,$updateSg);
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
		if($updateSg){//更新统计的sg数量(CTG中的好评数量)
			batchUpdate($updateSg,'id','client');
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
		$sql = "select client_id as id,rating,updated_rating,buyer_email  
				FROM review as a 
				join client_info as b on a.buyer_email=b.email 
				where (updated_rating = 0 and rating <= 3) or (updated_rating > 0 and updated_rating <= 3 )";
		Log::Info($sql);
		$reviewData = $this->queryRows($sql);
		foreach($reviewData as $key=>$val){
			$update[$val['id']]['id'] = $val['id'];
			$update[$val['id']]['times_negative_review'] = isset($update[$val['id']]['times_negative_review']) ? $update[$val['id']]['times_negative_review'] + 1 : 1;//差评次数
		}

		// Log::Info($update);
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

	/*
	 * 更新rsg_status字段和rsg_status_explain字段
	 * 步骤：1，先把这两个字段置为默认值
	 * 		2，优先级从低到高地查询并更新这两个字段（数字越小优先级越高，所以先更新符合第6条的数据，最后更新符合第一条的数据）
	 */
	function updateRsgStatus()
	{
		//6,有活动不是Completed状态（包含9,10,2）的客户标记红色
//		$sql = "update client as t1, (
//					select client_id as id
//					from client_info as a
//					left join rsg_requests as b on b.customer_email = a.email
//					where step not in (2,9,10)
//				) as t2
//				SET rsg_status = 1,rsg_status_explain = 6 WHERE t1.id = t2.id ";
//		DB::select($sql);

		//5,留评率低于90%的客户标记红色,已留评(RSG)/总订单数(RSG),具体为ReviewID OR LINK /Order ID的总数
		// $sql = "update client as t1, (
		// 			select client_id as id,if(count(amazon_order_id)>0,count(review_url)/count(amazon_order_id),1) as review_rate
		// 			from client_info as a
		// 			left join rsg_requests as b on b.customer_email = a.email
		// 			group by client_id
		// 		) as t2
		// 		SET rsg_status = 1,rsg_status_explain = 5 WHERE t1.id = t2.id and review_rate < 0.9";
		// DB::select($sql);

		//4,最近30天有参与3次RSG的客户标记红色
//		$daysago30 = date('Y-m-d',time()-86400*30);
//		$sql = "update client as t1, (
//					select client_id as id,count(*) as rsg_num
//					from client_info as a
//					left join rsg_requests as b on b.customer_email = a.email
//					where created_at >= '".$daysago30."'
//					group by client_id order by rsg_num desc
//				) as t2
//				SET rsg_status = 1,rsg_status_explain = 4 WHERE t1.id = t2.id and rsg_num >= 3";
//		DB::select($sql);

		//3,留差评的客户标记红色
		$sql = "update client SET rsg_status = 1,rsg_status_explain = 3 WHERE times_negative_review > 0";
		DB::select($sql);

		//2,有过已付款未购买情况的客户标记红色,Submit order ID状态（5）和open dispute（11）
		$sql = "update client as t1, (
					select client_id as id 
					from client_info as a
					left join rsg_requests as b on b.customer_email = a.email 
					where step in (5,11)  
				) as t2 
				SET rsg_status = 1,rsg_status_explain = 2 WHERE t1.id = t2.id ";
		DB::select($sql);

		//1,标签为黑名单、客户账号留评被限制的客户标记红色
		$sql = "update client SET rsg_status = 1,rsg_status_explain = 1 WHERE (type like '%1%') or (type like '%2%')";
		DB::select($sql);

	}

}



