<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\PpcRequest;
use App\Models\PpcProfile;

class RequestPpcReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'request:ppcReport {--profileId=}';

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
        $adTypes = [
            'SProducts','SDisplay','SBrands'
        ];
        $profiles = PpcProfile::whereNotNull('refresh_token');
        if($profileId) $profiles = $profiles->where('profile_id',$profileId);
        $profiles = $profiles->get();
        foreach($profiles as $profile){
            $profileId = $profile->profile_id;
            $client = new PpcRequest($profileId);
            foreach($adTypes as $adType){
                $app = $client->request($adType);
				if($adType=='SBrands'){
					$result = $app->campaigns->listCampaignsV4Ex(['maxResults'=>100]);
					if(array_get($result,'success')==1){
						$datas = array_get($result,'response.campaigns');
						if(is_array($datas)){
							$model = '\App\Models\Ppc'.ucfirst(strtolower($adType)).'Campaign';
							foreach($datas as $data){
								$data = unCamelizeArr($data);
								$allow_fields = ['campaign_id','profile_id','name','budget','budget_type','start_date','end_date','serving_status','portfolio_id','state','bid_optimization','bid_multiplier','bid_adjustments','brand_entity_id','ad_format','creative','landing_page','supply_source'];
								foreach($data as $dk=>$dv){
									if(!in_array($dk,$allow_fields)) unset($data[$dk]);
								}
								$model::updateOrCreate(
									[
										'profile_id'=>$profileId,
										'campaign_id'=>$data['campaign_id'],
									],
									$data       
								);
							}
						}
					}
				}else{
					$result = $app->campaigns->listCampaignsEx();
					if(array_get($result,'success')==1){
						$datas = array_get($result,'response');
						if(is_array($datas)){
							$model = '\App\Models\Ppc'.ucfirst(strtolower($adType)).'Campaign';
							foreach($datas as $data){
								$data = unCamelizeArr($data);
								if(isset($data['rule_based_budget'])) unset($data['rule_based_budget']);
								$model::updateOrCreate(
									[
										'profile_id'=>$profileId,
										'campaign_id'=>$data['campaign_id'],
									],
									$data       
								);
							}
						}
					}
				
				}
                
				$result = $app->groups->listAdGroupsEx();
                if(array_get($result,'success')==1){
                    $datas = array_get($result,'response');
                    if(is_array($datas)){
                        $model = '\App\Models\Ppc'.ucfirst(strtolower($adType)).'AdGroup';
                        foreach($datas as $data){
                            $data = unCamelizeArr($data);
                            $model::updateOrCreate(
                                [
                                    'ad_group_id'=>$data['ad_group_id'],
                                ],
                                $data       
                            );
                        }
                    }
                }
                if($adType!='SBrands'){
                    $result = $app->product_ads->listProductAds([]);
                    if(array_get($result,'success')==1){
                        $datas = array_get($result,'response');
                        if(is_array($datas)){
                            $model = '\App\Models\Ppc'.ucfirst(strtolower($adType)).'Ad';
                            foreach($datas as $data){
                                $data = unCamelizeArr($data);
                                $model::updateOrCreate(
                                    [
                                        'ad_id'=>$data['ad_id'],
                                    ],
                                    $data      
                                );
                            }
                        }
                    }  
                }
                
                if($adType!='SDisplay'){
                    $result = $app->keywords->listBiddableKeywordsEx([]);
                    if(array_get($result,'success')==1){
                        $datas = array_get($result,'response');
                        if(is_array($datas)){
                            $model = '\App\Models\Ppc'.ucfirst(strtolower($adType)).'Keyword';
                            foreach($datas as $data){
                                $data = unCamelizeArr($data);
                                $model::updateOrCreate(
                                    [
                                        'keyword_id'=>$data['keyword_id'],
                                    ],
                                    $data       
                                );
                            }
                        }
                    }
                    
                }    
                

                if($adType=='SBrands'){
                    $params = [];
                    $params['nextToken'] ='';
                    $params['maxResults'] = 5000;
                    do{
                        $result = $app->product_targeting->listTargetingClauses($params);
                        if(array_get($result,'success')==1){
                            $datas = array_get($result,'response.targets');
                            if(is_array($datas)){
                                $params['nextToken'] = array_get($result,'response.nextToken');
                                $model = '\App\Models\Ppc'.ucfirst(strtolower($adType)).'Target';
                                foreach($datas as $data){
                                    $data = unCamelizeArr($data);
                                    $model::updateOrCreate(
                                        [
                                            'target_id'=>$data['target_id'],
                                        ],
                                        $data       
                                    );
                                } 
                            }  
                        }
                    }while(!empty($params['nextToken']));
                }else{
                    if($adType=='SProducts') $result = $app->product_targeting->listTargetingClausesEx([]);
                    if($adType=='SDisplay') $result = $app->targeting->listTargetingClausesEx([]);
                    if(array_get($result,'success')==1){
                        $datas = array_get($result,'response');
                        if(is_array($datas)){
                            $model = '\App\Models\Ppc'.ucfirst(strtolower($adType)).'Target';
                            foreach($datas as $data){
                                $data = unCamelizeArr($data);
                                $model::updateOrCreate(
                                    [
                                        'target_id'=>$data['target_id'],
                                    ],
                                    $data     
                                );
                            }
                        }
                    }
                } 
            }
        }
     }

}
