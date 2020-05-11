<?php
/*
 * 此腳本是一次性腳本，删除历史non_ctg数据，删除掉不存在sap系统中的亚马逊订单号的non-ctg数据
 *
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;
use App\Classes\SapRfcRequest;

class DeleteNonctg extends Command
{

    protected $signature = 'delete:nonctg';

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
    //删除历史nonctg数据
    function handle()
    {
        $num = 200;
        set_time_limit(0);
        $today = date('Y-m-d');
        echo 'Execution delete_nonctg.php script start time:'.$today."\n";
        DB::connection()->enableQueryLog(); // 开启查询日志
        $nonctg = DB::table('non_ctg')->get(array('id','amazon_order_id'))->toArray();
        $delIds = array();
        $sap = new SapRfcRequest();
        foreach($nonctg as $key=>$val){
            $amazonOrderId = $val->amazon_order_id;
            $id = $val->id;
            $p = '/\d{3}\-\d{7}\-\d{7}/';
            preg_match($p, $amazonOrderId, $match);
            if($match && strlen($amazonOrderId) == 19){
                try {
                    SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $amazonOrderId]));
                } catch (\Exception $e) {
                    $delIds[] = $id;
                    echo '异常的订单:',$amazonOrderId.',ID为：'.$id."\n";
                }
            }else{
                $delIds[] = $id;
                echo '异常的订单:',$amazonOrderId.',ID为：'.$id."\n";
            }
        }
        if($delIds){
            $_delIds = array_chunk(array_unique($delIds),$num,true);
            foreach($_delIds as $val){
                DB::table('non_ctg')->whereIn('id',$val)->delete();
            }
        }
        $queries = DB::getQueryLog(); // 获取查询日志
        var_dump($queries); // 即可查看执行的sql，传入的参数等等
    }
}



