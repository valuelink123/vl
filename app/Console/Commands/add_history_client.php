<?php
/*
 * 添加CRM模块的历史客户信息
 * CRM模块，总共涉及3个表client，client_info，client_order_info
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;
use App\Classes\SapRfcRequest;

class HistoryClient extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'add:historyClient';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	protected $sap = '';

	protected $num = 500;

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

	//添加历史客户数据
	function handle()
	{
		set_time_limit(0);
		$this->sap = new SapRfcRequest();
		DB::connection()->enableQueryLog(); // 开启查询日志
		Log::Info('Add History Client Start...');
		$this->getCtgCrm();
		$this->getNonCtg();
		$this->getEmailCrm();
		$this->getCallCrm();
		$this->getRsgCrm();
		$this->getReviewCrm();
	}

	/*
	 * CTG,CashBack,BOGO这三个模块的表结构完全一模一样，
	 * 此方法包含了这三个来源的数据
	 */
	function getCtgCrm()
	{
		$where = '1 = 1';
		$config = getCtgCashbackBogoConfig();//得到配置信息
		foreach($config as $condata){
			//sql查询数据，得到不存在的邮箱和订单号的数据，然后插入到CRM模块的相关表中
			$sql = "SELECT  t1.name,t1.email,t1.phone,t1.order_id as amazon_order_id,t1.created_at as date,t1.processor as processor,t3.CountryCode as country,t4.brands as brand 
					FROM {$condata['table']} t1
					LEFT JOIN (
					  	SELECT MarketPlaceId,SellerId,AmazonOrderId,CountryCode
					  FROM ctg_order
					  ) t3
					  ON t3.AmazonOrderId = t1.order_id
					LEFT JOIN (
						SELECT ANY_VALUE(SellerId) AS SellerId,ANY_VALUE(MarketPlaceId) AS MarketPlaceId,ANY_VALUE(AmazonOrderId) AS AmazonOrderId,GROUP_CONCAT(DISTINCT asin.brand) AS brands
						FROM ctg_order_item t4_1
						LEFT JOIN asin
						  ON asin.site = t4_1.MarketPlaceSite AND asin.asin = t4_1.ASIN AND asin.sellersku = t4_1.SellerSKU
						GROUP BY MarketPlaceId,AmazonOrderId,SellerId
					  ) t4
					  ON t4.AmazonOrderId = t1.order_id AND t4.MarketPlaceId = t3.MarketPlaceId AND t4.SellerId = t3.SellerId
					left join client_info as t5 on t5.email = t1.email 
					left join client_order_info as t6 on t6.amazon_order_id=t1.order_id 
					WHERE $where and t1.email is not null and t6.id is null";
			Log::Info($sql);
			$data = $this->queryRows($sql);
			if($data){
				$this->addData($data, $condata['from'],false,false);
			}
		}
		// $queries = DB::getQueryLog(); // 获取查询日志
		// Log::Info($queries);
	}

	/*
	 * 得到Non-CTG数据
	 *
	 */
	function getNonCtg()
	{
		$where = '1 = 1';
		//sql查询数据，得到不存在的邮箱和订单号的数据，然后插入到CRM模块的相关表中
		$sql = "SELECT  t1.name,t1.email as email,t1.amazon_order_id as amazon_order_id,t1.processor as processor,t1.date as date,t3.brand as brand,'' as phone,'' as country  
				FROM non_ctg as t1 
				LEFT JOIN asin t3 ON t1.asin = t3.asin and t3.site = CONCAT('www.',t1.saleschannel) and t1.sellersku = t3.sellersku
				left join client_info as t5 on t5.email = t1.email 
				left join client_order_info as t6 on t6.amazon_order_id=t1.amazon_order_id 
				WHERE {$where} and t1.email is not null and t6.id is null";
		Log::Info($sql);
		$data = $this->queryRows($sql);
		if($data){
			$this->addData($data,'Non-CTG',true,false);
		}

		// $queries = DB::getQueryLog(); // 获取查询日志
		// Log::Info($queries);
	}
	/*
	 * 得到crm模块来自email客户
	 * 只获取site站点邮箱的客户
	 * 来自email的数据的processor为最后一个发件人
	 * 如果发件邮箱的后缀存在收件箱后缀中，则忽略掉这些邮箱发来的数据
	 *
	 */
	function getEmailCrm()
	{
		//先找出所有收件邮箱的后缀，如果发件邮箱的后缀存在这些后缀中，则忽略掉这些邮箱发来的数据
		$sql = 'SELECT distinct(to_address) as to_address 
				FROM inbox
				WHERE type = "Site"';
		$ignoreData = $this->queryRows($sql);

		$where = '1 = 1';
		$str = '';
		//NOT REGEXP '北京|上海|深圳|天津|香港|沈阳';
		foreach($ignoreData as $key=>$val){
			$v = explode('@',$val['to_address']);
			if(isset($v[1]) && $v[1]){
				$str .= $v[1].'|';
			}
		}
		if($str){
			$where = $where . ' AND t1.from_address NOT REGEXP "'.rtrim($str,'|').'"';
		}

		$sql = "SELECT  t1.from_name as name,t1.from_address as email,t1.to_address as to_email,t1.amazon_order_id as amazon_order_id,t1.date as date,t3.brand as brand,'' as phone,'' as country
				FROM inbox as t1
				LEFT JOIN asin t3 ON t1.asin = t3.asin and t1.sku = t3.sellersku
				left join client_info as t5 on t5.email = t1.from_address
				left join client_order_info as t6 on t6.amazon_order_id=t1.amazon_order_id
				WHERE {$where} and t1.from_address is not null 
				and t1.type = 'Site' and t6.id is null 
				order by date asc";

		Log::Info($sql);
		$_data = $this->queryRows($sql);
		$data = array();
		if($_data){
			$brandProcessor = getBrandProcessor();
			foreach($_data as $key=>$val){
				$to_email = explode('@',$val['to_email']);
				//得到邮箱对应的负责人，以最后一封邮件为准
				if(isset($to_email[1]) && $to_email[1]){
					$emailProcess[$val['email']] = $val['processor'] = isset($brandProcessor[$to_email[1]]) ? $brandProcessor[$to_email[1]] : 31;//如果没有配置对应品牌的负责人的话默认是靖晓菲(31)
				}
				$emailProcess[$val['email']] = $val['processor'];
				$data[$val['email'].'_'.$val['amazon_order_id']] = $val;//需要插入的数据，只有当邮箱和订单号同时存在的时候才不需要插入到client表中
			}
			foreach($data as $key=>$val){
				$data[$key]['processor'] = isset($emailProcess[$val['email']]) ? $emailProcess[$val['email']] : '';
			}
			$this->addData($data, 'Email',true,true);
		}

		// $queries = DB::getQueryLog(); // 获取查询日志
		// Log::Info($queries);

	}

	/*
	 * 得到crm模块来自Call客户
	 * 来自call的数据的processor为记录人(页面上的creator)
	 */
	function getCallCrm()
	{
		$where = '1 = 1';
		$sql = "SELECT  '' as name,t1.buyer_email as email,t1.amazon_order_id as amazon_order_id,t1.user_id as processor,t1.date as date,t3.brand as brand,'' as phone,'' as country 
				FROM phone as t1 
				LEFT JOIN asin t3 ON t1.asin = t3.asin and t1.sku = t3.sellersku 
				left join client_info as t5 on t5.email = t1.buyer_email 
				left join client_order_info as t6 on t6.amazon_order_id=t1.amazon_order_id 
				WHERE {$where} and t1.buyer_email is not null and t6.id is null";
		Log::Info($sql);
		$data = $this->queryRows($sql);
		if($data){
			$this->addData($data,'Call',true,false);
		}

		// $queries = DB::getQueryLog(); // 获取查询日志
		// Log::Info($queries);
	}

	/*
	 * 得到crm模块来自RSG客户
	 * RSG，默认没有processor
	 * 这个RSG的产品是这个销售的  但是客户不是他跟进的
	 * brand,phone,country,processor需要通过sap接口获取订单的信息
	 */
	function getRsgCrm()
	{
		$where = '1 = 1';
		$sql = "SELECT  '' as name,t1.customer_email as email,t1.amazon_order_id as amazon_order_id,t1.created_at as date,0 as processor,'' as brand,'' as phone,'' as country 
				FROM rsg_requests as t1 
				left join client_info as t5 on t5.email = t1.customer_email 
				left join client_order_info as t6 on t6.amazon_order_id=t1.amazon_order_id 
				WHERE {$where} and t1.customer_email is not null and t6.id is null";
		Log::Info($sql);
		$data = $this->queryRows($sql);
		if($data){
			$this->addData($data,'RSG',true,true);
		}

		// $queries = DB::getQueryLog(); // 获取查询日志
		// Log::Info($queries);
	}

	/*
	 * 得到crm模块来自review客户
	 * processor为页面上的user
	 */
	function getReviewCrm()
	{
		$where = '1 = 1';
		$sql = "SELECT  reviewer_name as name,t1.buyer_email as email,t1.amazon_order_id as amazon_order_id,t1.user_id as processor,t1.date as date,'' as brand,t1.buyer_phone as phone,'' as country 
				FROM review as t1 
				left join client_info as t5 on t5.email = t1.buyer_email 
				left join client_order_info as t6 on t6.amazon_order_id=t1.amazon_order_id 
				WHERE {$where} and t1.buyer_email is not null and t6.id is null";
		Log::Info($sql);
		$data = $this->queryRows($sql);
		if($data){
			$this->addData($data,'Review',true,true);
		}

		// $queries = DB::getQueryLog(); // 获取查询日志
		// Log::Info($queries);
	}

	//插入数据到client,client_info,client_order_info这三个表中
	function addData($_data,$from,$sap=false,$info=false)
	{
		DB::beginTransaction();
		$insertOrder = array();
		$data = array();
		//处理数据，一个email下可能有好几个订单
		foreach($_data as $key=>$val){
			if($sap){
				$val = $this->OrderInfoByData($val,$info);
			}
			$order_id = $val['amazon_order_id'];
			unset($val['amazon_order_id']);
			if(!isset($data[$val['email']])){
				$data[$val['email']] = $val;
			}

			$data[$val['email']]['amazon_order_id'][] = $order_id;
		}

		foreach($data as $key=>$val){
			//检查是否有相同的邮箱,email要保持唯一性
			$info = DB::table('client_info')->where('email',$val['email'])->get(array('id'))->toArray();
			if($info){
				$res = 1;
				$ci_id = $info[0]->id;
			}else{
				$insertInfo = array(
					'name'=>$val['name'],
					'email'=>$val['email'],
					'phone'=>$val['phone'],
					'country'=>$val['country'],
					'brand'=>$val['brand'],
					'from'=>$from,
				);
				//插入到client表里的数据0
				$userId = $val['processor'];
				$insertClient = array(
					'date'=>isset($val['date']) ? $val['date'] : date('Y-m-d H:i:s'),
					'created_at'=>date('Y-m-d H:i:s'),
					'updated_at'=>date('Y-m-d H:i:s'),
					'processor' => $userId,
				);

				$insertInfo['client_id'] = $res = DB::table('client')->insertGetId($insertClient);
				$ci_id = DB::table('client_info')->insertGetId($insertInfo);
			}
			if(empty($res) || empty($ci_id)){
				DB::rollBack();
				continue;
			}
			foreach($val['amazon_order_id'] as $v){
				if($v){
					//RSG的订单要特殊标记
					$order_type = 0;
					if($from=='RSG'){
						$order_type = 1;
					}
					$insertOrder[] = array(
						'amazon_order_id' => $v,
						'ci_id' => $ci_id,
						'order_type' => $order_type,
					);

				}
			}
		}
		//添加crm的订单信息表
		if($insertOrder){
			batchInsert('client_order_info',$insertOrder);
		}
		DB::commit();
	}

	/*
	 * 从sap的获取订单信息接口得到表里面没有的数据，例如country，phone
	 */
	function OrderInfoByData($data,$info)
	{
		$sap = $this->sap;

		$orderid = $data['amazon_order_id'];
		$match = matchOrderId($orderid);
		if($orderid){
			if($match){
				try {
					$sapOrderInfo = SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $orderid]));
					$data['country'] = isset($data['country']) && $data['country'] ? $data['country'] : $sapOrderInfo['CountryCode'];
					$data['phone'] = isset($data['phone']) && $data['phone'] ? $data['phone'] : $sapOrderInfo['Phone'];
					$data['name'] = isset($data['name']) && $data['name'] ? $data['name'] : $sapOrderInfo['Name'];
					$data['processor'] = isset($data['processor']) && $data['processor'] ? $data['processor'] : 0;
					if($info && isset($sapOrderInfo['orderItems'][0]['ASIN'])){
						$sql = "select t2.id as user_id,t1.brand as brand
										from asin as t1
										left join users as t2 on t2.sap_seller_id = t1.sap_seller_id
										where asin = '{$sapOrderInfo['orderItems'][0]['ASIN']}' and site = 'www.{$sapOrderInfo['SalesChannel']}' and sellersku = '{$sapOrderInfo['orderItems'][0]['SellerSKU']}' limit 1";
						$userData = $this->queryRows($sql);
						if($data['processor']==0){
							$data['processor'] = isset($userData[0]['user_id']) ? $userData[0]['user_id'] : 0;
						}
						if(empty($data['brand'])){
							$data['brand'] = isset($userData[0]['brand']) ? $userData[0]['brand'] : '';
						}
					}
				} catch (\Exception $e) {
					$data['amazon_order_id'] = '';
				}
			}else{
				$data['amazon_order_id'] = '';
			}
		}
		return $data;
	}
}



