<?php

namespace App\Console;

use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App;
use PDO;
use DB;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\GetEmails',
        'App\Console\Commands\SendEmails',
        'App\Console\Commands\Warning',
        'App\Console\Commands\AutoReply',
		'App\Console\Commands\GetReview',
        'App\Console\Commands\GetReviewTranslate',
		'App\Console\Commands\GetStar',
		'App\Console\Commands\GetAsin',
		'App\Console\Commands\GetOrder',
		'App\Console\Commands\GetSellers',
		'App\Console\Commands\GetAsininfo',
		'App\Console\Commands\GetAds',
		'App\Console\Commands\GetProfits',
        'App\Console\Commands\GetSettlementReport',
        'App\Console\Commands\GetAwsInfo',
		'App\Console\Commands\GetSales28day',
		'App\Console\Commands\GetShoudafang',
        'App\Console\Commands\Nonctg',
        'App\Console\Commands\UpdateNonctg',
        'App\Console\Commands\DeleteNonctg',
		'App\Console\Commands\HistoryClient',
		'App\Console\Commands\StaClient',
		'App\Console\Commands\AddClient',
		'App\Console\Commands\GetSkuBaseInfo',
		'App\Console\Commands\AddTransferWarn',
		'App\Console\Commands\SkuDaily',
		'App\Console\Commands\AddRsgProduct',
		'App\Console\Commands\GetRequestReviewTasks',
		'App\Console\Commands\SendEdmMailchimp',
		'App\Console\Commands\AddSalesRemind',
		'App\Console\Commands\AddAsinData',
		'App\Console\Commands\PushDailyReport',
		'App\Console\Commands\CalDailySales',
//		'App\Console\Commands\PullMailchimpData',
		'App\Console\Commands\McfOrderUpdateAmazonOrderId',
//		'App\Console\Commands\McfOrderUpdateSapStatus',
		'App\Console\Commands\UpdateEmails',
		'App\Console\Commands\UpdateEmailStatus',
		'App\Console\Commands\UpdateReportData',
		'App\Console\Commands\SycSendEmails',
		'App\Console\Commands\AddRoiPerformance',
		'App\Console\Commands\MakeReport',
		'App\Console\Commands\UpdateSendboxStatus',
		'App\Console\Commands\RequestPpcReport',
		'App\Console\Commands\RequestMonthPpcReport',
		'App\Console\Commands\GetPpcReport',
		'App\Console\Commands\GetPpcSchedule',
		'App\Console\Commands\AddSapInventory',
		'App\Console\Commands\AddAsins',
		'App\Console\Commands\AddPpcAdCampaign',
		'App\Console\Commands\ExceptionMcfInfo',
		'App\Console\Commands\WarrantyUpdateNonctg',
		'App\Console\Commands\AddPpcAdCampaignHistory',
		'App\Console\Commands\CsPhone',
		'App\Console\Commands\PostDa'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 由于 php artisan 命令会触发 schedule 执行；

        // if (!Schema::hasTable('accounts')) return;
		$schedule->command('add:cs_phone')->hourly()->name('add_cs_phone')->withoutOverlapping();//添加客服系统的call message数据

        // 防止第一次执行 php artisan migrate 时，报找不到表的错误；
		$schedule->call(function (){
			DB::update('update rsg_products set daily_remain = daily_stock;');
        })->dailyAt('14:00');
		$schedule->call(function (){
			DB::update("update rsg_products set status = 3 where status>-1 and end_date<'".date('Y-m-d')."';");
        })->dailyAt('03:00');
		$schedule->call(function (){
			DB::update("update budget_skus,asin set budget_skus.sap_seller_id=asin.sap_seller_id,budget_skus.`status`=asin.item_status,
budget_skus.bg=asin.bg, budget_skus.bu=asin.bu WHERE budget_skus.sku = asin.item_no and budget_skus.site=asin.site;");
        })->hourly();

        $accountList = DB::table('accounts')->get(array('id'));
        $i=$x=0;
        foreach($accountList as $account){
            if($i>59) $i=0;
			$num_i = sprintf("%02d",$i);
            $schedule->command('get:email '.$account->id)->cron($num_i.' * * * *')->name($account->id.'_get_emails_0')->withoutOverlapping();
			//$schedule->command('get:email '.$account->id)->cron(($num_i+30).' * * * *')->name($account->id.'_get_emails_30')->withoutOverlapping();
			$schedule->command('get:email '.$account->id.' --time=1day')->cron($x.' 18 * * *')->name($account->id.'_get_emails_18')->withoutOverlapping();
			$schedule->command('get:email '.$account->id.' --time=1day')->cron($x.' 6 * * *')->name($account->id.'_get_emails_6')->withoutOverlapping();
			$i++;
		if($x>9) $x=0;
		$x++;
        }
		$accountList = DB::table('accounts')->whereRaw("type='Amazon' or (bg is not null and bg<>'BG1')")->get(array('id'));
        $x=0;
		foreach($accountList as $account){
			if($x>9) $x=0;//每十分钟发送一次,$x>9就置0
			$num_x = sprintf("%02d",$x);
			$schedule->command('scan:send '.$account->id)->cron($num_x.' * * * *')->name($account->id.'sendmails_9')->withoutOverlapping();
			$schedule->command('scan:send '.$account->id)->cron(($num_x+10).' * * * *')->name($account->id.'sendmails_19')->withoutOverlapping();
			$schedule->command('scan:send '.$account->id)->cron(($num_x+20).' * * * *')->name($account->id.'sendmails_29')->withoutOverlapping();
			$schedule->command('scan:send '.$account->id)->cron(($num_x+30).' * * * *')->name($account->id.'sendmails_39')->withoutOverlapping();
			$schedule->command('scan:send '.$account->id)->cron(($num_x+40).' * * * *')->name($account->id.'sendmails_49')->withoutOverlapping();
			$schedule->command('scan:send '.$account->id)->cron(($num_x+50).' * * * *')->name($account->id.'sendmails_59')->withoutOverlapping();
			$x++;
        }


		$schedule->command('get:order')->cron('*/30 * * * *')->name('getOrder')->withoutOverlapping();
		$schedule->command('get:review 1days')->cron('0 */1 * * *')->name('getreviews')->withoutOverlapping();
		//$schedule->command('get:reviewTranslate')->cron('*/1 * * * *')->name('getreviewTranslate')->withoutOverlapping();
		$schedule->command('get:star 5days')->twiceDaily(11, 23)->name('getstars')->withoutOverlapping();
		$schedule->command('get:asin 3000 0')->cron('30 */3 * * *')->name('getasins')->withoutOverlapping();
		$schedule->command('get:kunnr 3 0')->hourly()->name('getkunnrs')->withoutOverlapping();
		//$schedule->command('get:sellers')->cron('*/1 * * * *')->name('sendmails')->withoutOverlapping();
		$schedule->command('get:asininfo')->cron('0 */2 * * *')->name('getasininfo')->withoutOverlapping();
		$schedule->command('request:ppcReport')->cron('0 */4 * * *')->name('requestppc')->withoutOverlapping();
		$schedule->command('request:monthPpcReport')->dailyAt('01:00')->name('requestppcmonth')->withoutOverlapping();
		$schedule->command('get:ppcReport --skip='.intval(date('i')))->cron(intval(date('i')).' * * * *')->name('getppc'.intval(date('i')))->withoutOverlapping();
		$schedule->command('get:ppcSchedule')->cron('*/4 * * * *')->name('scheduleppc')->withoutOverlapping();
		$schedule->command('get:ads 10 1')->cron('5 0 * * *')->name('getads')->withoutOverlapping();
		$schedule->command('get:profits 10 1 ')->cron('10 0 * * *')->name('getprotit')->withoutOverlapping();
        //$schedule->command('scan:warn')->hourly()->name('warningcheck')->withoutOverlapping();
        //$schedule->command('scan:auto')->hourly()->name('autocheck')->withoutOverlapping();
        $schedule->command('get:awsinfo')->dailyAt('23:00')->name('getawsinfo')->withoutOverlapping();
		$schedule->command('get:dailysales 7')->dailyAt('1:00')->name('getdailysales')->withoutOverlapping();
		//$schedule->command('get:requestreviewtasks')->dailyAt('18:00')->name('getrr')->withoutOverlapping();
        $filePath = base_path().'/storage/logs/noctg.log';
        // $schedule->command('add:nonctg')->monthly()->appendOutputTo($filePath)->name('add_history_nonctg')->withoutOverlapping();//添加nonctg数据，此脚本只执行一次
		$schedule->command('update:warranty_nonctg')->dailyAt('06:40')->appendOutputTo($filePath)->name('update_warranty_nonctg')->withoutOverlapping();//添加nonctg数据，此脚本只执行一次
        $schedule->command('update:nonctg')->cron('*/30 * * * *')->appendOutputTo($filePath)->name('update_nonctg')->withoutOverlapping();
        //crm模块的相关批处理
		$schedule->command('add:historyClient')->monthly()->name('add_history_client')->withoutOverlapping();//添加客户数据，此脚本只执行一次
		$schedule->command('sta:client')->hourly()->name('sta_client')->withoutOverlapping();//统计历史客户数据，每小时跑一次
		$schedule->command('add:client')->dailyAt('6:00')->name('add_client')->withoutOverlapping();//添加客户数据，每天跑一次，改成每15分钟一次
		$schedule->command('insert:asininfo')->dailyAt('3:00')->name('insertasin')->withoutOverlapping();
		$schedule->command('get:skubaseinfo')->dailyAt('16:00')->name('skubaseinfo')->withoutOverlapping();
		$schedule->command('add:transfer_warn')->dailyAt('6:00')->name('transferWarn')->withoutOverlapping();//添加调拨预警，每天跑一次
		$schedule->command('scan:skudaily')->dailyAt('08:00')->name('skudaily')->withoutOverlapping();
		$schedule->command('add:rsgProduct')->dailyAt('01:30')->name('addProduct')->withoutOverlapping();

		$schedule->command('send:mailchimp')->hourly()->name('sendMailchimp')->withoutOverlapping();//添加edmcampaign的时候，设置了发送时间，批处理发送edm邮件
		$schedule->command('add:sales_remind')->dailyAt('07:15')->name('addSalesRemind')->withoutOverlapping();//22周销售计划中，还没有填写销售计划的时候，插件提醒销售去添加计划
		$schedule->command('add:asin_data')->dailyAt('07:30')->name('addAsinData')->withoutOverlapping();//添加asin数据，每日更新，每周库存跟在途等数据

		$schedule->command('push:dailyReport --marketplace_id=ATVPDKIKX0DER --with_stock=1')->twiceDaily(14, 16)->name('dailyReportATVPDKIKX0DER')->withoutOverlapping();
		$schedule->command('push:dailyReport --marketplace_id=A2EUQ1WTGCTBG2 --with_stock=1')->twiceDaily(14, 16)->name('dailyReportA2EUQ1WTGCTBG2')->withoutOverlapping();
		$schedule->command('push:dailyReport --marketplace_id=A1AM78C64UM0Y8 --with_stock=1')->twiceDaily(14, 16)->name('dailyReportA1AM78C64UM0Y8')->withoutOverlapping();
		$schedule->command('push:dailyReport --marketplace_id=A1F83G8C2ARO7P --with_stock=1')->twiceDaily(9,11)->name('dailyReportA1F83G8C2ARO7P')->withoutOverlapping();
		$schedule->command('push:dailyReport --marketplace_id=APJ6JRA9NG5V4 --with_stock=1')->twiceDaily(9,11)->name('dailyReportAPJ6JRA9NG5V4')->withoutOverlapping();
		$schedule->command('push:dailyReport --marketplace_id=A13V1IB3VIYZZH --with_stock=1')->twiceDaily(9,11)->name('dailyReportA13V1IB3VIYZZH')->withoutOverlapping();
		$schedule->command('push:dailyReport --marketplace_id=A1PA6795UKMFR9 --with_stock=1')->twiceDaily(9,11)->name('dailyReportA1PA6795UKMFR9')->withoutOverlapping();
		$schedule->command('push:dailyReport --marketplace_id=A1RKKUPIHCS9HS --with_stock=1')->twiceDaily(9,11)->name('dailyReportA1RKKUPIHCS9HS')->withoutOverlapping();
		$schedule->command('push:dailyReport --marketplace_id=A1VC38T7YXB528 --with_stock=1')->twiceDaily(9,11)->name('dailyReportA1VC38T7YXB528')->withoutOverlapping();
		$schedule->command('update:mcf_order_amazonorderid')->monthly()->name('mcf_order_amazonorderid');

		$schedule->command('cal:dailySales')->dailyAt('08:30')->name('dailySales')->withoutOverlapping();
		//$schedule->command('sync:sendmail')->everyTenMinutes()->name('syncSendMail')->withoutOverlapping();
		//$schedule->command('update:reportData')->dailyAt('07:30')->name('reportData')->withoutOverlapping();
//		$schedule->command('update:emails')->monthly()->name('updateEmails')->withoutOverlapping();

		//cron('*/10 * * * *')
		//$schedule->command('update:email_status')->cron('*/10 * * * *')->name('update_email_status')->withoutOverlapping();//更新邮箱状态，每10分钟一次

		$schedule->command('add:roi_performance')->dailyAt('06:30')->name('add_roi_performance')->withoutOverlapping();//每天早上6点半执行

		//$schedule->command('update:sendboxStatus')->cron('0 */2 * * *')->name('updateSendboxStatus')->withoutOverlapping();//2小时执行一次

		$schedule->command('add:sap_inventory')->hourly()->name('add_sap_inventory')->withoutOverlapping();//每小时更新一次，用于获取sap库存数据，主要用于库存盘点模块
		$schedule->command('add:asins')->dailyAt('03:30')->name('addAsins')->withoutOverlapping();//添加asins数据，每日更新，
		$schedule->command('add:ppc_ad_campaign')->dailyAt('04:30')->name('addPpcAdCampaign')->withoutOverlapping();//添加广告映射关系数据，每日更新，
		$schedule->command('add:ppc_ad_campaign_history')->monthly()->name('addPpcAdCampaignHistory')->withoutOverlapping();//把销售之前维护的ppc_ad_match_asin表里面的映射关系，迁移到新的表里面，ppc_ad_campaign和ppc_ad_campaign_match_asin表

        $schedule->command('sync:purchase')->dailyAt('08:00')->name('sync_sap_purchase')->withoutOverlapping();//每天早上6点半执行
		$schedule->command('post:da')->hourly()->name('postda')->withoutOverlapping();
		$schedule->command('exception:mcfInfo')->hourly()->name('add_exception_mcfInfo')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
