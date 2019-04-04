<?php
/*
 * 官网库wp_gf_entry_meta表中meta_key=1.3为姓名，等于2位邮箱，等于3为亚马逊邓丹id(需配置)
 * 此腳本是一次性腳本，添加歷史non_ctg数据，此数据表示参加激活质保但是没有参加CTG活动的用户信息
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;


class Nonctg extends Command
{

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
    //添加历史nonctg数据
    function handle()
    {
        $num = 200;
        set_time_limit(0);
        $today = date('Y-m-d');
        echo 'Execution update_nonctg.php script start time:'.$today."\n";
        DB::connection()->enableQueryLog(); // 开启查询日志
        $config = getActiveUserConfig();//得到配置信息

        //凌晨跑前一天的数据，遍历各个官网的前一天的数据，再根据邮箱判断是否存在存在ctg表中，如果不存在就插入到nonctg数据表中
        foreach($config as $key=>$val){
            //遍历循环，一个一个官网进行处理
            $insertData = $data = $emailArr = array();
            $sql = "select entry_id, meta_key,meta_value,date_created 
            from {$val['dbname']}.wp_gf_entry as a
            left join {$val['dbname']}. wp_gf_entry_meta as b on a.id = b.entry_id 
            where b.form_id in (".join(',',$val['formid']).")";
            $_data = DB::connection('website')->select($sql);

            foreach($_data as $dk=>$dv){
                $data[$dv->entry_id]['date_created'] = $dv->date_created;
                $data[$dv->entry_id][$dv->meta_key] = $dv->meta_value;
                //key等于2为邮箱
                if($dv->meta_key==$val['fields']['email']){
                    $emailArr[$dv->entry_id] = $dv->meta_value;
                }
            }
            var_dump($data);
            //把$emailArr按个数分为n个数组，避免数据太多导致查询异常
            $emailArr = array_chunk(array_unique($emailArr),$num,true);
            foreach($emailArr as $email){
                //根据邮箱分批处理
                $ctgData = array();
                $_ctgData = DB::table('ctg')->whereIn('email',$email)->get(array('email'));
                foreach($_ctgData as $ck=>$cv){
                    $ctgData[] = $cv->email;
                }
                foreach($email as $ek=>$ev){
                    if(!in_array($ev,$ctgData)){//不在ctg中，应该插入到nonctg表中
                        $insertData[] = array(
                            'date' =>$data[$ek]['date_created'],
                            'name' => isset($data[$ek][$val['fields']['name']]) ? $data[$ek][$val['fields']['name']] : '未知',
                            'email' => isset($data[$ek][$val['fields']['email']]) ? $data[$ek][$val['fields']['email']] : '未知',
                            'amazon_order_id' => isset($data[$ek][$val['fields']['orderid']]) ? $data[$ek][$val['fields']['orderid']] : '未知',
                            'from' => $val['name'],
                        );
                    }
                }
                batchInsert('non_ctg',$insertData);//调用app/helper/functions.php的batchInsert方法插入数据,可以避免唯一键冲突
            }
        }
    }
}



