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

class Nonctg extends Command
{
	use \App\Traits\Mysqli;
    protected $signature = 'add:nonctg';

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
        $num = 200;
        set_time_limit(0);
        $today = date('Y-m-d');
        echo 'Execution add_history_nonctg.php script start time:'.$today."\n";
        DB::connection()->enableQueryLog(); // 开启查询日志
		$config = array(
			array('db'=>'website','dbname'=>'`vip-support_jp`','name'=>'vip-support.jp','formid'=>array(1),'fields'=>array('name'=>'2.3','email'=>3,'orderid'=>4)),
		);
        $sap = new SapRfcRequest();
        $date = '2020-07-01 00:00:00';//需要跑的历史记录时间

        //遍历各个官网的前一天的数据，再根据订单号判断是否存在ctg表中，如果不存在就插入到nonctg数据表中
        foreach($config as $key=>$val){
			try {
				//遍历循环，一个一个官网进行处理
				$entry_table = isset($val['entry_table']) && $val['entry_table'] ? $val['entry_table'] : 'wp_gf_entry';
				$meta_table = isset($val['meta_table']) && $val['meta_table'] ? $val['meta_table'] : 'wp_gf_entry_meta';
				$insertData = $data = $orderidArr = array();
				$sql = "select entry_id, meta_key,meta_value,date_created 
				from {$val['dbname']}.{$entry_table} as a
				left join {$val['dbname']}.{$meta_table} as b on a.id = b.entry_id 
				where b.form_id in (".join(',',$val['formid']).")
				and date_created >= '{$date}'";
				$_data = DB::connection($val['db'])->select($sql);

				foreach($_data as $dk=>$dv){
					$data[$dv->entry_id]['date_created'] = $dv->date_created;
					$data[$dv->entry_id][$dv->meta_key] = $dv->meta_value;
					//key等于2为邮箱
					if($dv->meta_key==$val['fields']['orderid']){
						$orderidArr[$dv->entry_id] = $dv->meta_value;
					}
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
											'date' =>$data[$ek]['date_created'],
											'name' => isset($data[$ek][$val['fields']['name']]) ? $data[$ek][$val['fields']['name']] : '未知',
											'email' => isset($data[$ek][$val['fields']['email']]) ? $data[$ek][$val['fields']['email']] : '未知',
											'amazon_order_id' => $orderid,
											'from' => $val['name'],
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
			}catch(\Exception $e){
				echo $val['name'],'异常';
			}
        }
        $queries = DB::getQueryLog(); // 获取查询日志
        var_dump($queries); // 即可查看执行的sql，传入的参数等等
        echo 'Execution script end';
    }
}



