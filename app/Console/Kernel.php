<?php

namespace App\Console;

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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $accountList = DB::table('accounts')->get(array('id'));
        $i=0;
        foreach($accountList as $account){
            if($i>59) $i=0;
            $schedule->command('get:email '.$account->id.' 6hour')->cron($i.' * * * *')->name($account->id.'_get_emails')->withoutOverlapping();
	//$schedule->command('get:email '.$account->id.' 6hour')->cron((30+$i).' * * * *')->name($account->id.'_get_emails')->withoutOverlapping();
            $i++;
        }
        //$schedule->command('scan:email')->hourly()->name('scanmails')->withoutOverlapping();
        $schedule->command('scan:send')->cron('*/5 * * * *')->name('sendmails')->withoutOverlapping();
		$schedule->command('get:order')->cron('*/30 * * * *')->name('getOrder')->withoutOverlapping();
		
		$schedule->command('get:review 7days')->cron('0 */4 * * *')->name('getreviews')->withoutOverlapping();
		
		$schedule->command('get:star 7days')->twiceDaily(20, 22)->name('getstars')->withoutOverlapping();
		$schedule->command('get:asin 3 0')->hourly()->name('getasins')->withoutOverlapping();
		$schedule->command('get:sellers')->cron('*/1 * * * *')->name('sendmails')->withoutOverlapping();
		$schedule->command('get:asininfo')->cron('30 0 * * *')->name('getasininfo')->withoutOverlapping();
		$schedule->command('get:ads 10 1')->cron('5 0 * * *')->name('getads')->withoutOverlapping();
		$schedule->command('get:profits 10 1 ')->cron('10 0 * * *')->name('getprotit')->withoutOverlapping();
        //$schedule->command('scan:warn')->hourly()->name('warningcheck')->withoutOverlapping();
        //$schedule->command('scan:auto')->hourly()->name('autocheck')->withoutOverlapping();
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
