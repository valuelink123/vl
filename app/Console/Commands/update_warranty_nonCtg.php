<?php
/*
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;
use App\Classes\SapRfcRequest;


class WarrantyUpdateNonctg extends Command
{
	use \App\Traits\Mysqli;
    protected $signature = 'update:warranty_nonctg {--date=}';

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
        $num = 200;
        set_time_limit(0);
        $today = date('Y-m-d');
        $yestoday = date('Y-m-d 00:00:00',strtotime("-1 day"));
		$date =$this->option('date');
		if($date){
			$yestoday = date('Y-m-d 00:00:00',strtotime($date));
		}
		Log::info('nonctg数据开始日期：'.$yestoday);
        // $yestoday = '2019-09-27 00:00:00';//测试数据
        echo 'Execution update_nonctg.php script start time:'.$today."\n";
		DB::connection()->enableQueryLog(); // 开启查询日志
		$sap = new SapRfcRequest();

		//遍历各个官网的前一天的数据，再根据订单号判断是否存在ctg表中，如果不存在就插入到nonctg数据表中
		$insertData = $data = $orderidArr = array();
		$sql = "select * from customers where created_at >= '".$yestoday."'";
		echo $sql;
		$_data = DB::connection('website')->select($sql);

		foreach($_data as $dk=>$dv){
			$data[$dv->amazon_order_id] = (array)$dv;
			$orderidArr[$dv->amazon_order_id] = $dv->amazon_order_id;
		}
		if($orderidArr){
			//把$orderidArr按个数分为n个数组，避免数据太多导致查询异常
			$orderidArr = array_chunk(array_unique($orderidArr),$num,true);
			foreach($orderidArr as $orderids){
				//根据邮箱分批处理
				$ctgData = array();
				$_ctgData = DB::table('ctg')->whereIn('order_id',$orderids)->get(array('order_id'));
				foreach($_ctgData as $ck=>$cv){
					$ctgData[] = $cv->order_id;
				}
				foreach($orderids as $ek=>$orderid){
					if(!in_array($orderid,$ctgData)){//不在ctg中，判断并获取订单号信息
						//判断订单号是否是存在的订单号
						$match = matchOrderId($orderid);
						if($match){
							try {
								$userid = 0;
								$sapOrderInfo = SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $orderid]));
								if(isset($sapOrderInfo['orderItems'][0]['ASIN'])){
									$sql = "select t2.id as user_id
										from asin as t1
										left join users as t2 on t2.sap_seller_id = t1.sap_seller_id
										where asin = '{$sapOrderInfo['orderItems'][0]['ASIN']}' and site = 'www.{$sapOrderInfo['SalesChannel']}' and sellersku = '{$sapOrderInfo['orderItems'][0]['SellerSKU']}' limit 1";
									$userData = $this->queryRows($sql);
									$userid = isset($userData[0]['user_id']) ? $userData[0]['user_id'] : 0;
								}

								$insertData[] = array(
									'date' =>$data[$ek]['created_at'],
									'name' => isset($data[$ek]['name']) ? $data[$ek]['name'] : '未知',
									'email' => isset($data[$ek]['email']) ? $data[$ek]['email'] : '未知',
									'amazon_order_id' => $orderid,
									'from' => $data[$ek]['brand_name'],
									'sellersku' => $sapOrderInfo['orderItems'][0]['SellerSKU'],
									'asin' => $sapOrderInfo['orderItems'][0]['ASIN'],
									'saleschannel' => $sapOrderInfo['SalesChannel'],
									'processor' => $userid,
								);
							} catch (\Exception $e) {
								echo '不添加异常的订单号：'.$orderid."\n";
							}
						}
					}
				}
				if($insertData){
					batchInsert('non_ctg',$insertData);//调用app/helper/functions.php的batchInsert方法插入数据,可以避免唯一键冲突
				}
			}
		}

		$queries = DB::getQueryLog(); // 获取查询日志
		var_dump($queries); // 即可查看执行的sql，传入的参数等等
		echo 'Execution script end';
    }
}



