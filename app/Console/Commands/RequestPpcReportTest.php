<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\PpcRequest;
use App\Models\PpcReport;
use App\Models\PpcProfile;
use DB;
use Log;

class RequestPpcReportTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'request:ppcReportTest {--profileId=} {--date=}';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	set_time_limit(0);
        $profileId = $this->option('profileId');
        $date = $this->option('date');
        if(!$date) $date = date('Y-m-d',strtotime('-1 day'));
        $metrics = '';
        $adTypes = [
            'SBrands'=>[
                'campaigns'=>[
                    [
                        'creativeType'=>'all',
                        'metrics'=>'campaignId,impressions,clicks,cost,attributedSales14d,attributedConversions14d'
                    ]
                ]
            ]
        ];
        $profiles = PpcProfile::whereNotNull('refresh_token');
        if($profileId) $profiles = $profiles->where('profile_id',$profileId);
        $profiles = $profiles->orderBy('updated_at','asc')->get();
		
        foreach($profiles as $profile){
            $profileId = $profile->profile_id;
            $client = new PpcRequest($profileId);
            foreach($adTypes as $adType=>$recordTypes){
                $app = $client->request($adType); 
                foreach($recordTypes as $recordType => $datas){
                    foreach($datas as $data){
						$getDate = 1;
						while($getDate<=38){
							$result = $app->report->requestReport(
								$recordType,
								array_merge($data,['reportDate'=>date('Ymd',strtotime('-'.$getDate.' days'))])
							);
							print_r($result);
							if(array_get($result,'success')==1){
								PpcReport::create(
									[
										'profile_id'=>$profileId,
										'report_id'=>array_get($result,'response.reportId'),
										'report_date'=>date('Y-m-d',strtotime('-'.$getDate.' days')),
										'record_type'=>array_get($result,'response.recordType'),
										'ad_type'=>$adType,
										'status'=>array_get($result,'response.status'),
									]
								);
							}
							$getDate++;
						}
                    }
                    
                }
            }
        }
	}

}
