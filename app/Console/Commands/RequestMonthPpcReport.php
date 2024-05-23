<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\PpcRequest;
use App\Models\PpcReport;
use App\Models\PpcProfile;
use DB;
use Log;

class RequestMonthPpcReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'request:monthPpcReport {--profileId=}';

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
        $metrics = 'impressions,clicks,cost,attributedConversions1d,attributedConversions1dSameSKU,attributedUnitsOrdered1d,attributedSales1d,attributedSales1dSameSKU,attributedConversions7d,attributedConversions7dSameSKU,attributedUnitsOrdered7d,attributedSales7d,attributedSales7dSameSKU,attributedConversions14d,attributedConversions14dSameSKU,attributedUnitsOrdered14d,attributedSales14d,attributedSales14dSameSKU,attributedConversions30d,attributedConversions30dSameSKU,attributedUnitsOrdered30d,attributedSales30d,attributedSales30dSameSKU';
        $adTypes = [
            'SProducts'=>[
                'campaign'=>[
					'configuration'=>[
						'adProduct'=>'SPONSORED_PRODUCTS',
						'groupBy'=>['campaign'],
						'columns'=>['campaignId','impressions','clicks','cost','purchases1d','purchasesSameSku1d','unitsSoldClicks1d','sales1d','attributedSalesSameSku1d','purchases7d','purchasesSameSku7d','unitsSoldClicks7d','sales7d','attributedSalesSameSku7d','purchases14d','purchasesSameSku14d','unitsSoldClicks14d','sales14d','attributedSalesSameSku14d','purchases30d','purchasesSameSku30d','unitsSoldClicks30d','sales30d','attributedSalesSameSku30d','unitsSoldSameSku1d','unitsSoldSameSku7d','unitsSoldSameSku14d','unitsSoldSameSku30d'],
						"reportTypeId"=>"spCampaigns",
						"timeUnit"=>"DAILY",
						"format"=>"GZIP_JSON",
					],	
                ],
                'adGroup'=>[
					'configuration'=>[
						'adProduct'=>'SPONSORED_PRODUCTS',
						'groupBy'=>['adGroup'],
						'columns'=>['adGroupId','impressions','clicks','cost','purchases1d','purchasesSameSku1d','unitsSoldClicks1d','sales1d','attributedSalesSameSku1d','purchases7d','purchasesSameSku7d','unitsSoldClicks7d','sales7d','attributedSalesSameSku7d','purchases14d','purchasesSameSku14d','unitsSoldClicks14d','sales14d','attributedSalesSameSku14d','purchases30d','purchasesSameSku30d','unitsSoldClicks30d','sales30d','attributedSalesSameSku30d','unitsSoldSameSku1d','unitsSoldSameSku7d','unitsSoldSameSku14d','unitsSoldSameSku30d'],
						"reportTypeId"=>"spCampaigns",
						"timeUnit"=>"DAILY",
						"format"=>"GZIP_JSON",
					],
                ],
                'keyword'=>[
					'configuration'=>[
						'adProduct'=>'SPONSORED_PRODUCTS',
						'groupBy'=>['searchTerm'],
						'columns'=>['keywordId','impressions','clicks','cost','purchases1d','purchasesSameSku1d','unitsSoldClicks1d','sales1d','attributedSalesSameSku1d','purchases7d','purchasesSameSku7d','unitsSoldClicks7d','sales7d','attributedSalesSameSku7d','purchases14d','purchasesSameSku14d','unitsSoldClicks14d','sales14d','attributedSalesSameSku14d','purchases30d','purchasesSameSku30d','unitsSoldClicks30d','sales30d','attributedSalesSameSku30d','unitsSoldSameSku1d','unitsSoldSameSku7d','unitsSoldSameSku14d','unitsSoldSameSku30d'],
						"reportTypeId"=>"spSearchTerm",
						"timeUnit"=>"DAILY",
						"format"=>"GZIP_JSON",
					],
                ],
                'productAd'=>[
					'configuration'=>[
						'adProduct'=>'SPONSORED_PRODUCTS',
						'groupBy'=>['advertiser'],
						'columns'=>['adId','impressions','clicks','cost','purchases1d','purchasesSameSku1d','unitsSoldClicks1d','sales1d','attributedSalesSameSku1d','purchases7d','purchasesSameSku7d','unitsSoldClicks7d','sales7d','attributedSalesSameSku7d','purchases14d','purchasesSameSku14d','unitsSoldClicks14d','sales14d','attributedSalesSameSku14d','purchases30d','purchasesSameSku30d','unitsSoldClicks30d','sales30d','attributedSalesSameSku30d','unitsSoldSameSku1d','unitsSoldSameSku7d','unitsSoldSameSku14d','unitsSoldSameSku30d'],
						"reportTypeId"=>"spAdvertisedProduct",
						"timeUnit"=>"DAILY",
						"format"=>"GZIP_JSON",
					],
                ],
                'targets'=>[
					'configuration'=>[
						'adProduct'=>'SPONSORED_PRODUCTS',
						'groupBy'=>['targeting'],
						'columns'=>['keywordId','impressions','clicks','cost','purchases1d','purchasesSameSku1d','unitsSoldClicks1d','sales1d','attributedSalesSameSku1d','purchases7d','purchasesSameSku7d','unitsSoldClicks7d','sales7d','attributedSalesSameSku7d','purchases14d','purchasesSameSku14d','unitsSoldClicks14d','sales14d','attributedSalesSameSku14d','purchases30d','purchasesSameSku30d','unitsSoldClicks30d','sales30d','attributedSalesSameSku30d','unitsSoldSameSku1d','unitsSoldSameSku7d','unitsSoldSameSku14d','unitsSoldSameSku30d'],
						"reportTypeId"=>"spTargeting",
						"timeUnit"=>"DAILY",
						"format"=>"GZIP_JSON",
					],
                ],
            ],
            'SDisplay'=>[
                'campaigns'=>[
                    [
                        'metrics'=>'campaignId,costType,viewAttributedConversions14d,viewAttributedSales14d,viewAttributedUnitsOrdered14d,viewImpressions,'.$metrics,
                        'tactic'=>'T00020'
                    ],
                    [
                        'metrics'=>'campaignId,costType,viewAttributedConversions14d,viewAttributedSales14d,viewAttributedUnitsOrdered14d,viewImpressions,'.$metrics,
                        'tactic'=>'T00030'
                    ]
                ],
                'adGroups'=>[
                    [
                        'metrics'=>'adGroupId,viewAttributedConversions14d, viewAttributedSales14d, viewAttributedUnitsOrdered14d, viewImpressions,'.$metrics,
                        'tactic'=>'T00020'
                    ],
                    [
                        'metrics'=>'adGroupId,viewAttributedConversions14d, viewAttributedSales14d, viewAttributedUnitsOrdered14d, viewImpressions,'.$metrics,
                        'tactic'=>'T00030'
                    ]
                ],
                'productAds'=>[
                    [
                        'metrics'=>'adId,viewAttributedConversions14d, viewAttributedSales14d, viewAttributedUnitsOrdered14d, viewImpressions,'.$metrics,
                        'tactic'=>'T00020'
                    ],
                    [
                        'metrics'=>'adId,viewAttributedConversions14d, viewAttributedSales14d, viewAttributedUnitsOrdered14d, viewImpressions,'.$metrics,
                        'tactic'=>'T00030'
                    ]
                ],
                'targets'=>[
                    [
                        'metrics'=>'targetId,viewAttributedConversions14d, viewAttributedSales14d, viewAttributedUnitsOrdered14d, viewImpressions,'.$metrics,
                        'tactic'=>'T00020'
                    ],
                    [
                        'metrics'=>'targetId,viewAttributedConversions14d, viewAttributedSales14d, viewAttributedUnitsOrdered14d, viewImpressions,'.$metrics,
                        'tactic'=>'T00030'
                    ]
                ]
            ],
            
            'SBrands'=>[
                'campaigns'=>[
                    [
                        'creativeType'=>'all',
                        'metrics'=>'campaignId,impressions,clicks,cost,attributedSales14d,attributedConversions14d'
                    ]
                ],
                'adGroups'=>[
                    [
                        'creativeType'=>'all',
                        'metrics'=>'adGroupId,'.$metrics.',attributedUnitsOrdered1dSameSKU,attributedUnitsOrdered7dSameSKU,attributedUnitsOrdered14dSameSKU,attributedUnitsOrdered30dSameSKU'
                    ]
                ],
                'keywords'=>[
                    [
                        'creativeType'=>'all',
                        'metrics'=>'keywordId,'.$metrics.',attributedUnitsOrdered1dSameSKU,attributedUnitsOrdered7dSameSKU,attributedUnitsOrdered14dSameSKU,attributedUnitsOrdered30dSameSKU'
                    ]
                ],
                'targets'=>[
                    [
                        'creativeType'=>'all',
                        'metrics'=>'targetId,'.$metrics.',attributedUnitsOrdered1dSameSKU,attributedUnitsOrdered7dSameSKU,attributedUnitsOrdered14dSameSKU,attributedUnitsOrdered30dSameSKU'
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
						$getDates = [1,2,3,13,14,15,29,30,31];
						foreach($getDates as $k=>$getDate){
							if($adType =='SProducts'){
								$result = $app->report->requestReport(
									$recordType,
									array_merge(['configuration'=>$data],[
										'name'=>'Sp Reports '.$recordType.' '.date('Y-m-d',strtotime('-'.$getDate.' days')).' '.date('YmdHis'),
										'startDate'=>date('Y-m-d',strtotime('-'.$getDate.' days')),
										'endDate'=>date('Y-m-d',strtotime('-'.$getDate.' days')),
									])
								);
								Log::info(json_encode($result));
								if(array_get($result,'success')==1){
									$a = PpcReport::create(
										[
											'profile_id'=>$profileId,
											'report_id'=>array_get($result,'response.reportId'),
											'report_date'=>date('Y-m-d',strtotime('-'.$getDate.' days')),
											'record_type'=>$recordType,
											'ad_type'=>$adType,
											'status'=>array_get($result,'response.status'),
										]
									);
									Log::info(json_encode($a));
								}
								
							}else{
								$result = $app->report->requestReport(
									$recordType,
									array_merge($data,['reportDate'=>date('Ymd',strtotime('-'.$getDate.' days'))])
								);
								Log::info(json_encode($result));
								if(array_get($result,'success')==1){
									$a = PpcReport::create(
										[
											'profile_id'=>$profileId,
											'report_id'=>array_get($result,'response.reportId'),
											'report_date'=>date('Y-m-d',strtotime('-'.$getDate.' days')),
											'record_type'=>array_get($result,'response.recordType'),
											'ad_type'=>$adType,
											'status'=>array_get($result,'response.status'),
										]
									);
									Log::info(json_encode($a));
								}
							}
							
						}
				 
                    }
                    
                }
            }
        }
	}

}
