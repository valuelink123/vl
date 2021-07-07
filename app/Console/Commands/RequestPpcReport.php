<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\PpcRequest;
use App\Models\PpcReport;
use DB;
use Log;

class RequestPpcReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'request:ppcReport {--profileId=} {--date=}';

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
        $profileId = $this->option('profileId');
        $date = $this->option('date');
        if(!$date) $date = date('Y-m-d',strtotime('-20 hours'));
        $client = new PpcRequest($profileId);
        $client->refreshToken();
        $metrics = 'impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU,attributedUnitsOrdered1dSameSKU';
        $adTypes = [
            'SProducts'=>[
                'campaigns'=>[
                    ['metrics'=>'campaignId,'.$metrics]
                ],
                'adGroups'=>[
                    ['metrics'=>'adGroupId,'.$metrics]
                ],
                'keywords'=>[
                    ['metrics'=>'keywordId,'.$metrics]
                ],
                'productAds'=>[
                    ['metrics'=>''.$metrics]
                ],
                'targets'=>[
                    ['metrics'=>'targetId,'.$metrics]
                ],
            ],
            
            'SDisplay'=>[
                'campaigns'=>[
                    [
                        'metrics'=>'campaignId,impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU',
                        'tactic'=>'T00020'
                    ],
                    [
                        'metrics'=>'campaignId,impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU',
                        'tactic'=>'T00030'
                    ],
                    [
                        'metrics'=>'campaignId,impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU',
                        'tactic'=>'remarketing'
                    ],
                ],
                'adGroups'=>[
                    [
                        'metrics'=>'adGroupId,impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU',
                        'tactic'=>'T00020'
                    ],
                    [
                        'metrics'=>'adGroupId,impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU',
                        'tactic'=>'T00030'
                    ],
                    [
                        'metrics'=>'adGroupId,impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU',
                        'tactic'=>'remarketing'
                    ],
                ],
                'productAds'=>[
                    [
                        'metrics'=>'adId,impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU',
                        'tactic'=>'T00020'
                    ],
                    [
                        'metrics'=>'adId,impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU',
                        'tactic'=>'T00030'
                    ],
                    [
                        'metrics'=>'adId,impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU',
                        'tactic'=>'remarketing'
                    ],
                ],
                'targets'=>[
                    [
                        'metrics'=>'targetId,impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU',
                        'tactic'=>'T00020'
                    ],
                    [
                        'metrics'=>'targetId,impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU',
                        'tactic'=>'T00030'
                    ],
                ],
            ],
            
            'SBrands'=>[
                'campaigns'=>[
                    [
                        'creativeType'=>'video',
                        'metrics'=>'campaignId,impressions,clicks,cost'
                    ],
                    [
                        'metrics'=>'campaignId,impressions,clicks,cost'
                    ]
                ],
                'adGroups'=>[
                    [
                        'creativeType'=>'video',
                        'metrics'=>'adGroupId,'.$metrics
                    ],
                    [
                        'metrics'=>'adGroupId,'.$metrics
                    ]
                ],
                'keywords'=>[
                    [
                        'creativeType'=>'video',
                        'metrics'=>'keywordId,'.$metrics
                    ],
                    [
                        'metrics'=>'keywordId,'.$metrics
                    ]
                ],
                'targets'=>[
                    [
                        'creativeType'=>'video',
                        'metrics'=>'targetId,'.$metrics
                    ],
                    [
                        'metrics'=>'targetId,'.$metrics
                    ]
                ],
            ]
        ];
        foreach($adTypes as $adType=>$recordTypes){
            $app = $client->request($adType); 
            foreach($recordTypes as $recordType => $datas){
                foreach($datas as $data){
                    $result = $app->report->requestReport(
                        $recordType,
                        array_merge($data,['reportDate'=>date('Ymd',strtotime($date))])
                    );
                    if(array_get($result,'success')==1){
                        PpcReport::create(
                            [
                                'profile_id'=>$profileId,
                                'report_id'=>array_get($result,'response.reportId'),
                                'report_date'=>$date,
                                'record_type'=>array_get($result,'response.recordType'),
                                'ad_type'=>$adType,
                                'status'=>array_get($result,'response.status'),
                            ]
                        );
                    }
                }
                
            }
        }
	}

}
