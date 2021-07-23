<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PpcReport;
use App\Models\PpcProfile;
use App\Models\PpcReportData;
use App\Classes\PpcRequest;
use DB;
use Log;

class GetPpcReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ppcReport {--profileId=}';

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
        $tasks = PpcReport::where('status','IN_PROGRESS');
        if($profileId) $tasks = $tasks->where('profile_id',$profileId);
        $tasks = $tasks->get();
        foreach($tasks as $task){
            DB::beginTransaction();
		    try{
                $client = new PpcRequest($task->profile_id);
                $app = $client->request($task->ad_type); 
                $result = $app->report->getReport($task->report_id);
                if(array_get($result,'success')!=1) continue;
                if(array_get($result,'response.status')!='SUCCESS') continue;
                $task->status = array_get($result,'response.status');
                $task->location = array_get($result,'response.location');
                $result = $app->report->downloadReportData($task->report_id,
                    [
                        'path'=>storage_path(),
                        'reportId'=>$task->report_id,
                    ]
                );
                if(array_get($result,'success')!=1) continue;
                $datas = array_get($result,'response');
                foreach($datas as $data){
                    $data = unCamelizeArr($data);
                    $recordType = $task->record_type;
                    if($task->record_type=='campaigns') $recordType = 'campaign';
                    if($task->record_type=='adGroups') $recordType = 'adGroup';
                    if($task->record_type=='keywords') $recordType = 'keyword';
                    if($task->record_type=='productAd') $recordType = 'ad';
                    if($task->record_type=='productAds') $recordType = 'ad';
                    if($task->record_type=='targets') $recordType = 'target';
                    $recordTypeId = array_get($data,unCamelize($recordType.'Id'));
                    unset($data[unCamelize($recordType.'Id')]);
                    PpcReportData::updateOrCreate(
                        [
                            'profile_id'=>$task->profile_id,
                            'ad_type'=>$task->ad_type,
                            'record_type'=>$recordType,
                            'record_type_id'=>$recordTypeId,
                            'date'=>$task->report_date,
                        ],
                        $data
                    );
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
