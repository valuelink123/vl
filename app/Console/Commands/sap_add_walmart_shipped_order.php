<?php
/*
 */
namespace App\Console\Commands;

use App\Classes\Walmart;
use Illuminate\Console\Command;
use DB;
use Log;
use App\Classes\SapRfcRequest;


class SapAddWalmartShippedOrder extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'add:walmart_order {--date=}';

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

	//更新nonctg数据
	function handle()
	{
		Log::info('Execution add:walmart_order script start'.date('Y-m-d H:i:s'));
		set_time_limit(0);
		$date = date('Y-m-d 00:00:00',strtotime("-7 day"));
		$date_param =$this->option('date');
		if($date_param){
			$date = date('Y-m-d 00:00:00',strtotime($date_param));
		}
		$walmart = new Walmart();
		$params['createdStartDate'] = $date;
		if($params['createdStartDate'] < '2023-06-20'){
			$params['createdStartDate'] = '2023-06-20 00:00:00';//测试数据
		}
		$params['createdStartDate'] = "2023-05-01";//测试数据
		$params['limit'] = 1000;
		$params['status'] = 'Shipped';//Delivered
		Log::info('params:'.json_encode($params));
		$account = DB::table('walmart_accounts')->where('account_status',1)->get();
		foreach($account as $key=>$val){
			$this->getWalmartOrderData($walmart,$val,$params);
		}
		Log::info('Execution script end');
	}

	public function getWalmartOrderData($walmart,$account_info,$params)
	{
		$orderList = $walmart->getOrderShipped($account_info,$params);
		if($orderList){
			$this->insertOrder($account_info,$orderList['elements']['order']);
			if($orderList && $orderList['meta']['nextCursor']){
				$url_next = $orderList['meta']['nextCursor'];
				$str=mb_substr($url_next,stripos($url_next,"?")+1);
				parse_str($str, $params_next);
				$this->getWalmartOrderData($walmart,$account_info,$params_next);
			}
		}
	}

	public function insertOrder($account_info,$data)
	{
		//循环遍历数据，插入到VOP的本地表中
		if($data){
			foreach($data as $dk=>$dv){
				//平台订单总金额等于各个订单总金额之和
				$amount_total = 0;
				foreach($dv['orderLines']['orderLine'] as $itemkey=>$itemval){
					$amount_total = $amount_total + $itemval['charges']['charge'][0]['chargeAmount']['amount'];
				}
				//主表数据
				$order = array(
					'ORDERID'=>$dv['purchaseOrderId'],
					'SELLERID'=>$account_info->account_sellerid,
					'ZMPLACEID'=>$account_info->account_sellerid,
					'PCHASEDATE'=>date('Ymd',$dv['orderDate']/1000-3600*15),
					'PCHASETIME'=>'',///////
					'LUPDATEDATE'=>date('Ymd',$dv['shippingInfo']['estimatedDeliveryDate']/1000-3600*15),//预计送达日期
					'LUPDATETIME'=>'',/////
					'ALOADDATE'=>date('Ymd',$dv['shippingInfo']['estimatedShipDate']/1000-3600*15),//预计发货日期
					'ALOADTIME' => '',/////
					'ZFLAG'=>'',/////
//					'ZFCHANNEL'=>'',
					'ZGOODSPRICE'=>$amount_total,//平台订单总金额(商品金额)lineNumber1-chargeAmount-amount+lineNumber2-chargeAmount-amount+lineNumber3-chargeAmount-amount
					'ZCURRENCY1'=>'USD',//币种
					'ZSHIPPINGOPTIONAMOUNT'=>'',/////
					'ZCURRENCY2'=>'',/////
					'ZTOTALPRICE'=>'',/////
					'ZCURRENCY3'=>'',/////
					'ZAMOUNT'=>'',/////
					'ZCURRENCY'=>'',/////
					'ZYHPRICE'=>'',
					'ZCURRENCY4'=>'',
					'ZJSSXPRICE'=>'',
					'ZCURRENCY5'=>'',
					'ZDGZFDJPRICE'=>'',
					'ZCURRENCY6'=>'',

					'ZSJFKPRICE'=>$amount_total,//lineNumber1-chargeAmount-amount+lineNumber2-chargeAmount-amount+lineNumber3-chargeAmount-amount
					'ZCURRENCY7'=>'USD',
					'ZKDH'=>$dv['customerOrderId'],//客户订单号
					'ADDR1'=>$dv['shippingInfo']['postalAddress']['address1'].$dv['shippingInfo']['postalAddress']['address2'],
					'ZCITY'=>$dv['shippingInfo']['postalAddress']['city'],
					'ZCOUNTRY'=>$dv['shippingInfo']['postalAddress']['country'],
					'ZDISTRICT'=>$dv['shippingInfo']['postalAddress']['state'],
					'ZPOSCODE'=>$dv['shippingInfo']['postalAddress']['postalCode'],
					'ZNAME'=>$dv['shippingInfo']['postalAddress']['name'],
					'ZPHONE'=>$dv['shippingInfo']['phone'],
					'ZEMAIL' => $dv['shippingInfo']['methodCode'],//平台运输方式
					'ZMARK'=>'',///
				);
				//明细表数据
				$order_item = array();
				$sap_order_item = array();
				foreach($dv['orderLines']['orderLine'] as $itemkey=>$itemval){
					$order_item[$itemkey] = array(
						'ORDERID'=>$dv['purchaseOrderId'],
						'ZORIID' => 10*(1+$itemkey),/////
						'ZMPLACEID'=>$account_info->account_sellerid,
						'SELLERID'=>$account_info->account_sellerid,
						'ZSSKU'=>$itemval['item']['sku'],
						'ZIPAMOUNT'=>$itemval['charges']['charge'][0]['chargeAmount']['amount'],//订单金额
						'ZIPCCODE'=>'USD',//
						'STAMOUNT'=>'',/////
						'STCCODE'=>'',/////
						'ZQORDERED'=>isset($itemval['orderLineStatuses']['orderLineStatus'][0]['statusQuantity']['amount']) ? $itemval['orderLineStatuses']['orderLineStatus'][0]['statusQuantity']['amount'] : '',//statusQuantity--amount,sku数量
						'ZQSHIPPED'=>isset($itemval['orderLineStatuses']['orderLineStatus'][0]['statusQuantity']['amount']) ? $itemval['orderLineStatuses']['orderLineStatus'][0]['statusQuantity']['amount'] : '',//发货数量
						'ZITEMID'=>isset($itemval['orderLineStatuses']['orderLineStatus'][0]['trackingInfo']['trackingNumber']) ? $itemval['orderLineStatuses']['orderLineStatus'][0]['trackingInfo']['trackingNumber'] : '',//trackingNumber,追踪号
						'ZTAXRATE'=>'',/////
						'ZMARK'=>'',/////
						'ZTITLE'=>$itemval['item']['productName'],
					);
					$sap_order_item[] = $order_item[$itemkey];
				}

				//查看此订单号在数据库里面是否有数据,没有历史数据才插入到表里面，才更新到sap里面去
				$_orderinfo = DB::table('walmart_shipped_order')->where('ORDERID',$dv['purchaseOrderId'])->first();
				if(!$_orderinfo){
					DB::table('walmart_shipped_order')->insert($order);
					DB::table('walmart_shipped_order_item')->insert($order_item);
					//推送到sap系统
					$sap_order[0] = $order;
					$sap = new SapRfcRequest();
					try {
//						$res=$sap->ZFMPHPRFC033(['sap_order'=>$sap_order,'sap_order_item'=>$sap_order_item]);
					} catch (\Exception $e) {

					}
				}
			}
		}
	}
}



