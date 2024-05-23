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
    protected $signature = 'get:ppcReport {--profileId=} {--skip=}';

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
		$count = ceil(PpcReport::where('status','IN_PROGRESS')->count()/60);
		if($count>500) $count=500;
		$skip = intval($this->option('skip'));
		if($skip>30) $skip=$skip-30;
        $tasks = PpcReport::where('status','IN_PROGRESS');
        if($profileId) $tasks = $tasks->where('profile_id',$profileId);
        $tasks = $tasks->orderBy('updated_at','asc')->skip($skip*$count)->take($count)->get();
        foreach($tasks as $task){
			try{
				$client = new PpcRequest($task->profile_id);
				$app = $client->request($task->ad_type);
				$result = $app->report->getReport($task->report_id);
				if(array_get($result,'success')!=1){
					$task->updated_at = date('Y-m-d H:i:s');
					$task->status = array_get($result,'code');
					$task->save();
					continue;
				} 
				$task->status = array_get($result,'response.status');
				if(array_get($result,'response.status')!='SUCCESS'){
					$task->updated_at = date('Y-m-d H:i:s');
					$task->save();
					continue;
				}
				$task->status = array_get($result,'response.status');
				$task->location = array_get($result,'response.location');
				$result = $app->report->downloadReportData($task->report_id,
					[
						'path'=>storage_path(),
						'reportId'=>$task->report_id,
					]
				);
				if(array_get($result,'success')!=1){
					$task->updated_at = date('Y-m-d H:i:s');
					$task->status = array_get($result,'code');
					$task->save();
					continue;
				}
			}catch (\Exception $e) { 
				//print_r($e);
                continue;
            } 
            
		    try{
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
            }catch (\Exception $e) { 
				Log::info(json_encode($a));
            } 
        }
		
		$count = ceil(PpcReport::where('ad_type','SProducts')->where('status','PENDING')->count()/60);
		if($count>500) $count=500;
		$skip = intval($this->option('skip'));
		if($skip>30) $skip=$skip-30;
        $tasks = PpcReport::where('ad_type','SProducts')->where('status','PENDING');
        if($profileId) $tasks = $tasks->where('profile_id',$profileId);
        $tasks = $tasks->orderBy('updated_at','asc')->skip($skip*$count)->take($count)->get();
        foreach($tasks as $task){
            $client = new PpcRequest($task->profile_id);
            $app = $client->request($task->ad_type);
            $result = $app->report->getReport($task->report_id);
            if(array_get($result,'success')!=1) continue;
            if(array_get($result,'response.status')!='COMPLETED') continue;
            $task->status = array_get($result,'response.status');
            $task->location = array_get($result,'response.url');
            $result = $app->report->downloadReportData($task->location,
                [
                    'path'=>storage_path(),
                    'reportId'=>$task->report_id,
                ]
            );
            if(array_get($result,'success')!=1) continue;
		    try{
                $datas = array_get($result,'response');
                foreach($datas as $data){
					if($task->record_type=='targets'){
						$data['targetId'] = $data['keywordId'];
						unset($data['keywordId']);
					}
					$resetKey = [
						'attributedConversions1d'=>'purchases1d',
						'attributedConversions7d'=>'purchases7d',
						'attributedConversions14d'=>'purchases14d',
						'attributedConversions30d'=>'purchases30d',
						'attributedConversions1dSameSKU'=>'purchasesSameSku1d',
						'attributedConversions7dSameSKU'=>'purchasesSameSku7d',
						'attributedConversions14dSameSKU'=>'purchasesSameSku14d',
						'attributedConversions30dSameSKU'=>'purchasesSameSku30d',
						'attributedUnitsOrdered1d'=>'unitsSoldClicks1d',
						'attributedUnitsOrdered7d'=>'unitsSoldClicks7d',
						'attributedUnitsOrdered14d'=>'unitsSoldClicks14d',
						'attributedUnitsOrdered30d'=>'unitsSoldClicks30d',
						'attributedSales1d'=>'sales1d',
						'attributedSales7d'=>'sales7d',
						'attributedSales14d'=>'sales14d',
						'attributedSales30d'=>'sales30d',
						'attributedSales1dSameSKU'=>'attributedSalesSameSku1d',
						'attributedSales7dSameSKU'=>'attributedSalesSameSku7d',
						'attributedSales14dSameSKU'=>'attributedSalesSameSku14d',
						'attributedSales30dSameSKU'=>'attributedSalesSameSku30d',
						'attributedUnitsOrdered1dSameSKU'=>'unitsSoldSameSku1d',
						'attributedUnitsOrdered7dSameSKU'=>'unitsSoldSameSku7d',
						'attributedUnitsOrdered14dSameSKU'=>'unitsSoldSameSku14d',
						'attributedUnitsOrdered30dSameSKU'=>'unitsSoldSameSku30d',
					];
					foreach($resetKey as $key=>$val){
						if(isset($data[$val])){
							$data[$key]	= $data[$val];
							unset($data[$val]);
						}
					}
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
            }catch (\Exception $e) { 
                Log::info(json_encode($e));
            } 
        
        }
        
	}

}
