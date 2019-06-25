<?php

namespace App\Console;

use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App;
use PDO;
use DB;
require __DIR__.'/../Helper/functions.php';
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\GetEmails',
        'App\Console\Commands\ScanEmails',
        'App\Console\Commands\SendEmails',
        'App\Console\Commands\Warning',
        'App\Console\Commands\AutoReply',
		'App\Console\Commands\GetReview',
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

        // 防止第一次执行 php artisan migrate 时，报找不到表的错误；
		$schedule->call(function (){
			DB::update('update rsg_products set daily_remain = daily_stock;');
        })->dailyAt('14:00');
		$schedule->call(function (){
			DB::update("update rsg_products set status = 3 where status>-1 and end_date<'".date('Y-m-d')."';");
        })->dailyAt('00:00');
        $accountList = DB::table('accounts')->get(array('id'));
        $i=0;
        foreach($accountList as $account){
            if($i>29) $i=0;
			$num_i = sprintf("%02d",$i);
            $schedule->command('get:email '.$account->id)->cron($num_i.' * * * *')->name($account->id.'_get_emails_0')->withoutOverlapping();
			$schedule->command('get:email '.$account->id)->cron($num_i+30.' * * * *')->name($account->id.'_get_emails_30')->withoutOverlapping();
            $i++;
        }

        $schedule->command('scan:send')->cron('*/5 * * * *')->name('sendmails')->withoutOverlapping();
		$schedule->command('get:order')->cron('*/30 * * * *')->name('getOrder')->withoutOverlapping();
		$schedule->command('get:review 7days')->cron('0 */4 * * *')->name('getreviews')->withoutOverlapping();
		$schedule->command('get:star 7days')->twiceDaily(20, 22)->name('getstars')->withoutOverlapping();
		$schedule->command('get:asin 3 0')->hourly()->name('getasins')->withoutOverlapping();
		$schedule->command('get:kunnr 3 0')->hourly()->name('getkunnrs')->withoutOverlapping();
		$schedule->command('get:sellers')->cron('*/1 * * * *')->name('sendmails')->withoutOverlapping();
		$schedule->command('get:asininfo')->cron('30 0 * * *')->name('getasininfo')->withoutOverlapping();
		$schedule->command('get:ads 10 1')->cron('5 0 * * *')->name('getads')->withoutOverlapping();
		$schedule->command('get:profits 10 1 ')->cron('10 0 * * *')->name('getprotit')->withoutOverlapping();
        //$schedule->command('scan:warn')->hourly()->name('warningcheck')->withoutOverlapping();
        //$schedule->command('scan:auto')->hourly()->name('autocheck')->withoutOverlapping();
        $schedule->command('get:awsinfo')->dailyAt('23:00')->name('getawsinfo')->withoutOverlapping();
		$schedule->command('get:dailysales 7')->dailyAt('1:00')->name('getdailysales')->withoutOverlapping();

        $filePath = base_path().'/storage/logs/noctg.log';
        // $schedule->command('add:nonctg')->monthly()->appendOutputTo($filePath)->name('add_history_nonctg')->withoutOverlapping();//添加nonctg数据，此脚本只执行一次
        $schedule->command('update:nonctg')->cron('*/30 * * * *')->appendOutputTo($filePath)->name('update_nonctg')->withoutOverlapping();
        //crm模块的相关批处理
		$schedule->command('add:historyClient')->monthly()->name('add_history_client')->withoutOverlapping();//添加客户数据，此脚本只执行一次
		$schedule->command('sta:client')->dailyAt('1:00')->name('sta_client')->withoutOverlapping();//统计历史客户数据，每天跑一次
		$schedule->command('add:client')->dailyAt('2:00')->name('add_client')->withoutOverlapping();//添加客户数据，每天跑一次
		$schedule->command('insert:asininfo')->dailyAt('3:00')->name('insertasin')->withoutOverlapping();
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
