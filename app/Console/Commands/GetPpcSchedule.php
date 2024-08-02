<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PpcReport;
use App\Models\PpcProfile;
use App\Models\PpcSchedule;
use App\Models\PpcReportData;
use App\Classes\PpcRequest;
use DB;
use Log;

class GetPpcSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ppcSchedule {--profileId=}';

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
        $date = date('Y-m-d');
        $time = date('Gi');
        $tasks = PpcSchedule::where('status',1)->where('date_from','<=',$date)
        ->where('date_to','>=',$date)->whereRaw("replace(`time`,':','')<=$time and (done_at<='$date' or done_at is null)");
        if($profileId) $tasks = $tasks->where('profile_id',$profileId);
        
        //$tasks = PpcSchedule::where('user_id',1);
        $tasks = $tasks->get();

        foreach($tasks as $task){
            $client = new PpcRequest($task->profile_id);
            $app = $client->request($task->ad_type); 
            DB::beginTransaction();
		    try{
                $data = [];
                
                if($task->record_type=='campaign'){
                    $action = 'campaigns';
                    $method = 'updateCampaigns';
                    $typeId = 'campaignId';
                    $bid = ($task->ad_type=='SProducts')?'dailyBudget':'budget';
                }
                if($task->record_type=='adGroup'){
                    $action = 'groups';
                    $method = 'updateAdGroups';
                    $typeId = 'adGroupId';
                    $bid = 'defaultBid';
                }
                if($task->record_type=='keyword'){
                    $action = 'keywords';
                    $method = 'updateKeywords';
                    $typeId = 'keywordId';
                    $bid = 'bid';
                    if($task->ad_type=='SBrands'){
                        $data['campaignId']=$task->campaign_id;
                        $data['adGroupId']=$task->adgroup_id;
                    }
                }
                if($task->record_type=='target'){
                    $action = ($task->ad_type=='SDisplay')?'targeting':'product_targeting';
                    $method = 'updateTargetingClauses';
                    $typeId = 'targetId';
                    $bid = 'bid';
                    if($task->ad_type=='SBrands'){
                        $data['campaignId']=$task->campaign_id;
                        $data['adGroupId']=$task->adgroup_id;
                    }
                }
                if($task->record_type=='theme'){
                    $action = 'themes';
                    $method = 'updateThemes';
                    $typeId = 'themeId';
                    $bid = 'bid';
                    if($task->ad_type=='SBrands'){
                        $data['campaignId']=$task->campaign_id;
                        $data['adGroupId']=$task->adgroup_id;
                    }
                }

                $data[$typeId] = (string)$task->record_type_id;
                $data['state'] = ($task->ad_type=='SBrands' && $task->record_type=='campaign')?strtoupper($task->state):$task->state;
                $data[$bid] = round($task->bid,2);
                $results = $app->$action->$method([$data]);
                if(array_get($results,'code') == 429){
                    $task->message = '429 Request Limited';	
                }else{
                    if(array_get($results,'success') == 1){
                        $task->done_at = date('Y-m-d H:i:s');
                        if($task->ad_type=='SBrands' && in_array($typeId,['adGroupId','campaignId'])){
                            if($typeId == 'adGroupId') $rk = 'adGroups';
                            if($typeId == 'campaignId') $rk = 'campaigns';
                            $successes = array_get($results,'response.'.$rk.'.success');
                            if(!empty($successes)){
                                $task->message = 'SUCCESS';	
                            }
                        }elseif($task->ad_type=='SBrands' && in_array($typeId,['themeId'])){
                            $successes = array_get($results,'response.success');
                            if(!empty($successes)){
                                $task->message = 'SUCCESS';	
                            }
                        }else{
                            foreach(array_get($results,'response') as $k=>$result){
                                if(strtolower(array_get($result,'code'))=='success'){
                                    $task->message = 'SUCCESS';	
                                    break;
                                }elseif(strtolower($k)=='success' && !empty($result)){
                                    $task->message = 'SUCCESS';	
                                    break;
                                }else{
                                    $task->message = json_encode(array_get($results,'response'));	
                                }
                            }
                        }
                    }else{
                        $task->message =json_encode(array_get($results,'response'));
                    }
                } 
                $task->save();
                DB::commit();
            }catch (\Exception $e) {
                print_r($e->getMessage());
                DB::rollBack();
            } 
        }
        
	}

}
