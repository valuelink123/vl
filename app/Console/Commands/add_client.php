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

class AddClient extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'add:client';

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

	//添加历史客户数据
	function handle()
	{
		$date = date('Y-m-d',time()-3600*24);
		set_time_limit(0);
		DB::connection()->enableQueryLog(); // 开启查询日志
		Log::Info('Add Client Start...');
		$this->getCtgCrm($date);
		$this->getNonCtg($date);
		$this->getEmailCrm($date);
		$this->getCallCrm($date);

	}

	/*
	 * CTG,CashBack,BOGO这三个模块的表结构完全一模一样，
	 * 此方法包含了这三个来源的数据
	 */
	function getCtgCrm($date)
	{
		$where = "created_at >= '{$date}'";
		$config = getCtgCashbackBogoConfig();//得到配置信息
		foreach($config as $condata){
			//sql查询数据，得到不存在的邮箱和订单号的数据，然后插入到CRM模块的相关表中
			$sql = "SELECT  t1.name,t1.email,t1.phone,t1.order_id as amazon_order_id,t3.County as country,t4.brands as brand 
					FROM {$condata['table']} t1
					LEFT JOIN (
					  	SELECT MarketPlaceId,SellerId,AmazonOrderId,County
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
				$dataArr = array_chunk($data,200,true);
				foreach($dataArr as $data) {
					$this->addData($data, $condata['from']);
				}
			}
		}
		$queries = DB::getQueryLog(); // 获取查询日志
		Log::Info($queries);
	}

	/*
	 * 得到Non-CTG数据
	 *
	 */
	function getNonCtg($date)
	{
		$where = "date >= '{$date}'";
		//sql查询数据，得到不存在的邮箱和订单号的数据，然后插入到CRM模块的相关表中
		$sql = "SELECT  t1.name,t1.email as email,t1.amazon_order_id as amazon_order_id,t3.brand as brand,'' as phone,'' as country  
				FROM non_ctg as t1 
				LEFT JOIN asin t3 ON t1.asin = t3.asin and t3.site = CONCAT('www.',t1.saleschannel) and t1.sellersku = t3.sellersku
				left join client_info as t5 on t5.email = t1.email 
				left join client_order_info as t6 on t6.amazon_order_id=t1.amazon_order_id 
				WHERE {$where} and t1.email is not null and t6.id is null";
		Log::Info($sql);
		$data = $this->queryRows($sql);
		if($data){
			$dataArr = array_chunk($data,200,true);
			foreach($dataArr as $data){
				$data = $this->OrderInfoByData($data);
				if($data){
					$this->addData($data,'Non-CTG');
				}
			}
		}

		$queries = DB::getQueryLog(); // 获取查询日志
		Log::Info($queries);
	}
	/*
	 * 得到crm模块来自email客户
	 * 只获取site站点邮箱的客户
	 */
	function getEmailCrm($date)
	{
		$where = "date >= '{$date}'";
		$sql = "SELECT  t1.from_name as name,t1.from_address as email,t1.amazon_order_id as amazon_order_id,t3.brand as brand,'' as phone,'' as country
				FROM inbox as t1 
				LEFT JOIN asin t3 ON t1.asin = t3.asin and t1.sku = t3.sellersku
				left join client_info as t5 on t5.email = t1.from_address 
				left join client_order_info as t6 on t6.amazon_order_id=t1.amazon_order_id 
				WHERE {$where} and t1.from_address is not null and type = 'Site' and t6.id is null ";
		Log::Info($sql);
		$data = $this->queryRows($sql);
		if($data){
			$data = $this->OrderInfoByData($data);
			$dataArr = array_chunk($data,200,true);
			foreach($dataArr as $data) {
				if ($data) {
					$this->addData($data, 'Email');
				}
			}
		}

		$queries = DB::getQueryLog(); // 获取查询日志
		Log::Info($queries);

	}

	/*
	 * 得到crm模块来自Call客户
	 *
	 */
	function getCallCrm($date)
	{
		$where = "date >= '{$date}'";
		$sql = "SELECT  '' as name,t1.buyer_email as email,t1.amazon_order_id as amazon_order_id,t3.brand as brand,'' as phone,'' as country 
				FROM phone as t1 
				LEFT JOIN asin t3 ON t1.asin = t3.asin and t1.sku = t3.sellersku
				left join client_info as t5 on t5.email = t1.buyer_email 
				left join client_order_info as t6 on t6.amazon_order_id=t1.amazon_order_id 
				WHERE {$where} and t1.buyer_email is not null and t6.id is null ";
		Log::Info($sql);
		$data = $this->queryRows($sql);
		if($data){
			$data = $this->OrderInfoByData($data);
			if($data){
				$this->addData($data,'Call');
			}
		}

		$queries = DB::getQueryLog(); // 获取查询日志
		Log::Info($queries);

	}

	//插入数据到client,client_info,client_order_info这三个表中
	function addData($_data,$from)
	{
		DB::beginTransaction();
		$insertOrder = array();
		$data = array();
		//处理数据，一个email下可能有好几个订单
		foreach($_data as $key=>$val){
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
				$insertInfo['client_id'] = $res = DB::table('client')->insertGetId(array('date'=>date('Y-m-d')));
				$ci_id = DB::table('client_info')->insertGetId($insertInfo);
			}
			if(empty($res) || empty($ci_id)){
				DB::rollBack();
				continue;
			}
			foreach($val['amazon_order_id'] as $v){
				if($v){
					$insertOrder[] = array(
						'amazon_order_id' => $v,
						'ci_id' => $ci_id,
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
	function OrderInfoByData($data)
	{
		$sap = new SapRfcRequest();
		foreach($data as $key=>$val){
			$orderid = $val['amazon_order_id'];
			$match = matchOrderId($orderid);
			if($orderid){
				if($match){
					try {
						$sapOrderInfo = SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $orderid]));
						$data[$key]['country'] = $val['country'] ? $val['country'] : $sapOrderInfo['CountryCode'];
						$data[$key]['phone'] = $val['phone'] ? $val['phone'] : $sapOrderInfo['Phone'];
						$data[$key]['name'] = $val['name'] ? $val['name'] : $sapOrderInfo['Name'];
					} catch (\Exception $e) {
						$data[$key]['amazon_order_id'] = '';
					}
				}else{
					$data[$key]['amazon_order_id'] = '';
				}
			}
		}
		return $data;
	}
}



