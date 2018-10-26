<?php
/**
 * Created by PhpStorm.
 * Author: lyl
 * Date: 2018/10/19
 * Time: 15:10
 * 从ccp获取cpc数据
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;

class GetAwsInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:awsinfo';

    /**
     *  访问公司账号，通过CCP数据库获取CPC信息
     *
     * @var string
     */
    protected $description = 'Get AWS report from CCP';

    public function __construct()
    {
        parent::__construct();

    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        //先从DB_ORDER_HOST 获取所有公司内部账号信息，然后循环获取信息
        $accounts = DB::connection('order')->select("select SellerId,MarketPlaceId from accounts where MarketPlaceId !=:mexico and Status =1",['mexico' => 'A1AM78C64UM0Y8']);
        $marketplaceid_arr=array(
            'A2EUQ1WTGCTBG2'=>2,
            'A1PA6795UKMFR9'=>4,
            'A1RKKUPIHCS9HS'=>5,
            'A13V1IB3VIYZZH'=>6,
            'APJ6JRA9NG5V4'=>8,
            'A1VC38T7YXB528'=>10,
            'A1F83G8C2ARO7P'=>9,
            'A1AM78C64UM0Y8'=>3,
            'ATVPDKIKX0DER'=>1
        );
        //获取数据开始日期
        $date_from = date('Y-m-d',strtotime('-2days'));
        $date_to = date('Y-m-d',strtotime('-1day'));
        if(isset($accounts) && !empty($accounts))
        {
            foreach($accounts as $account)
            {
                //将marketplaceid 与ccp中对应起来
                $marketplaceid =$marketplaceid_arr[$account->MarketPlaceId];
                $date = DB::select('select * from aws_report_time where seller_id=:seller_id and marketplace_id=:marketplace_id limit 1',['seller_id'=>$account->SellerId,'marketplace_id'=>$account->MarketPlaceId]);
                if(isset($date) && !empty($date))
                {
                    $date_from = $date[0]->date;
                    $date_to = date('Y-m-d',strtotime($date_from) + 3600*24);
                }
                $seller_id=$account->SellerId;

                //从CCP获取数据
                $reports = DB::connection("ccp")->select(
                    "select ppc_campaigns.name as campaign_name,ppc_ad_groups.name as ad_group,ppc_reports.date,sum(ppc_reports.cost) as cost,
                  sum(attributed_sales1d) as sales,sum(attributed_conversions1d_same_sku) as orders,sum(impressions) as impressions,sum(clicks) as  clicks,
                  ppc_ad_groups.default_bid,ppc_campaigns.state 
                  from ppc_reports
                  left join ppc_campaigns on ppc_campaigns.campaign_id=ppc_reports.record_type_id
                  LEFT JOIN ppc_profiles on ppc_profiles.profile_id = ppc_reports.profile_id
                  left join accounts on accounts.id=ppc_profiles.account_id
                  left join ppc_ad_groups on ppc_ad_groups.campaign_id = ppc_campaigns.campaign_id
                  where ppc_reports.date>'$date_from' and ppc_reports.date<='$date_to' and accounts.seller_id='$seller_id' and  accounts.marketplace_id=$marketplaceid  group by ppc_campaigns.name,ppc_ad_groups.ad_group_id "
                    );
                if(isset($reports) && !empty($reports))
                {
                    $updated_date =0;
                    DB::beginTransaction();
                    try{
                        foreach ($reports as $k=> $report)
                        {
                            if(!is_null($report->campaign_name)){
                                $info = array(
                                    "seller_id"=>$account->SellerId,
                                    "marketplace_id"=>$account->MarketPlaceId,
                                    "campaign_name"=>$report->campaign_name,
                                    "ad_group"=>$report->ad_group,
                                    "cost"=>$report->cost,
                                    "sales"=>$report->sales,
                                    "profit"=>$report->sales - $report->cost,
                                    "orders"=>$report->orders,
                                    "acos"=>$report->sales != 0 ? $report->cost/$report->sales :0,
                                    "impressions"=>$report->impressions,
                                    "clicks"=>$report->clicks,
                                    "ctr"=>$report->impressions != 0 ? $report->clicks/$report->impressions :0,
                                    "cpc"=>$report->clicks != 0 ? $report->cost/$report->clicks :0,
                                    "ad_conversion_rate"=>$report->clicks != 0 ? $report->orders/$report->clicks :0,
                                    "default_bid"=>$report->default_bid,
                                    "date"=>$report->date,
                                    "state"=>$report->state,
                                    "created_at"=>date("Y-m-d H:i:s"),
                                    "updated_at"=>date("Y-m-d H:i:s"),
                                );
                                $updated_date =$report->date;
                              DB::table('aws_report')->insert($info);
                            }
                        }
                        $time_arr = [$account->SellerId,$account->MarketPlaceId,$updated_date,date("Y-m-d H:i:s"),date("Y-m-d H:i:s")];
                        $time_info = "'".implode("','",$time_arr)."'";
                        DB::insert("INSERT INTO aws_report_time (seller_id,marketplace_id,date,created_at,updated_at) VALUES ($time_info) ON DUPLICATE KEY UPDATE `date`=VALUES (`date`),`updated_at`=VALUES (`updated_at`)");
                       DB::commit();
                    }catch (\Exception $e){
                        var_dump($e->getMessage());
                        DB::rollBack();
                    }
                }
            }
        }
    }
}