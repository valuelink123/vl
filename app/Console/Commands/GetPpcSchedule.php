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
        ->where('date_to','<=',$date)->whereRaw("replace(`time`,':','')<=$time and (done_at<='$date' or done_at is null)");
        if($profileId) $tasks = $tasks->where('profile_id',$profileId);
        $tasks = $tasks->get();
        foreach($tasks as $task){
            DB::beginTransaction();
            $customActionMessage = '';
		    try{
                $client = new PpcRequest($task->profile_id);
                $app = $client->request($task->ad_type); 
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
                }
                if($task->record_type=='target'){
                    $action = 'product_targeting';
                    $method = 'updateTargetingClauses';
                    $typeId = 'targetId';
                    $bid = 'bid';
                }
                $results = $app->$action->$method([[
                    $typeId=>$task->record_type_id,
                    'state'=>$task->state,
                    $bid=>round($task->bid,2),
                ]]);
                if(array_get($results,'success') == 1){
                    foreach(array_get($results,'response') as $result){
                        $task->message =$result['code'];
                    }
                }else{
                    $task->message =array_get($results,'response');
                }
                $task->done_at = date('Y-m-d H:i:s');
                $task->save();
                DB::commit();
            }catch (\Exception $e) { 
                Log::Info($e->getMessage());
                print_r($e->getMessage());
                DB::rollBack();
            } 
        }
        
	}

}
