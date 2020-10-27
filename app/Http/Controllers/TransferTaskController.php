<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Models\TransferTask;
use App\Models\TransferPlan;
use App\Models\TransferRequest;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Illuminate\Http\Response;
class TransferTaskController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     *
     */

    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index()
    {
        if(!Auth::user()->can(['transfer-task-show'])) die('Permission denied -- transfer-task-show');
        return view('transfer/taskList',['sellers'=>getUsers('sap_seller'), 'users'=>getUsers(), 'status'=>TransferTask::STATUS]);

    }

    public function get(Request $request)
    {
        $records = array();
        $datas = TransferTask::select('transfer_plans.*','transfer_tasks.id as id','transfer_requests.marketplace_id','transfer_requests.bg','transfer_requests.bu','transfer_requests.asin','transfer_requests.sku'
        ,'transfer_requests.quantity as request_quantity','transfer_tasks.transfer_task_key','transfer_tasks.status as task_status','transfer_tasks.carrier_code as task_carrier_code'
        ,'transfer_tasks.ship_method as task_ship_method','transfer_tasks.tracking_number','transfer_tasks.out_date as task_out_date','transfer_tasks.in_date as task_in_date')
        ->leftJoin('transfer_plans',function($q){
            $q->on('transfer_tasks.transfer_plan_id', '=', 'transfer_plans.id');
        })
        ->leftjoin('transfer_requests',function($q){
            $q->on('transfer_plans.transfer_request_id', '=', 'transfer_requests.id');
        });
        
        if(array_get($_REQUEST,'out_factory')){
            $datas = $datas->where('out_factory',array_get($_REQUEST,'out_factory'));
        }
        if(array_get($_REQUEST,'in_factory')){
            $datas = $datas->where('in_factory',array_get($_REQUEST,'in_factory'));
        }
        if(array_get($_REQUEST,'asin')){
            $datas = $datas->whereIn('transfer_requests.asin',explode(',',str_replace([' ','	'],'',array_get($_REQUEST,'asin'))));
        }
        if(array_get($_REQUEST,'sku')){
            $datas = $datas->whereIn('transfer_requests.sku',explode(',',str_replace([' ','	'],'',array_get($_REQUEST,'sku'))));
        } 
        if(array_get($_REQUEST,'status')!==NULL && array_get($_REQUEST,'status')!==''){
            $datas = $datas->whereIn('transfer_tasks.status',array_get($_REQUEST,'status'));
        }
        
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->orderBy('transfer_tasks.id','desc')->get()->toArray();
        $users = getUsers();
        $records["data"] = array();

		foreach ( $lists as $list){
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
                $list['out_factory'],
                $list['in_factory'],
                $list['asin'],
                $list['sku'],
                $list['quantity'],
                $list['carrier_code'].($list['ship_method']?'</BR>'.$list['ship_method']:''),
                $list['out_date'],
                $list['in_date'],
                $list['rms'],
                $list['require_attach']?'Y':'N',
                $list['require_purchase']?'Y':'N',
                $list['require_rebrand']?'Y':'N',
                $list['transfer_task_key'],
                ($list['task_status']!==NUll)?array_get(TransferTask::STATUS,$list['task_status']):'',
                ($list['tracking_number']?$list['tracking_number']:'').($list['task_carrier_code']?'</BR>'.$list['task_carrier_code']:'').($list['task_ship_method']?'</BR>'.$list['task_ship_method']:''),
                $list['task_out_date'],
                $list['task_in_date']
            );
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->can(['transfer-task-show'])) die('Permission denied -- transfer-task-show');
        $transferTask =  TransferTask::find($id);
        if(empty($transferTask)) die('计划不存在!');
        $transferPlan = TransferPlan::find($transferTask->transfer_plan_id);
        $transferRequest = TransferRequest::find($transferPlan->transfer_request_id);
        $users = getUsers();
        $siteCode = DB::table('marketplaces')->pluck('country_code','marketplace_id');
        $accountCode = DB::connection('amazon')->table('seller_accounts')->whereNull('deleted_at')->pluck('label','mws_seller_id');
        $logs = getOperationLog(['table'=>'transfer_tasks','primary_id'=>$id]);
        $logArr = [];
        foreach($logs  as $log){
            $logArr[]= $log->created_at.' '.array_get($users,$log->user_id).' '.array_get(TransferTask::STATUS,array_get(json_decode($log->input,true),'status')); 
        }
        return view('transfer/taskEdit',['transferPlan'=>$transferPlan,'transferRequest'=>$transferRequest,'transferTask'=>$transferTask,'sellers'=>getUsers('sap_seller'), 'users'=>$users, 'planStatus'=>TransferPlan::STATUS, 'requestStatus'=>TransferRequest::STATUS, 'taskStatus'=>TransferTask::STATUS,'logArr'=>$logArr,'siteCode'=>$siteCode,'accountCode'=>$accountCode]);
    }
	

    public function update(Request $request,$id)
    {
		if(!Auth::user()->can(['transfer-task-update'])) die('Permission denied -- transfer-task-update');
        DB::beginTransaction();
        try{ 
            $data = TransferTask::findOrFail($id);
            if($data->status != $request->get('status') ) saveOperationLog('transfer_tasks', $data->id, ['status'=>$request->get('status')]);
            $fileds = array(
                'out_date','in_date','tracking_number','status'
            );
            foreach($fileds as $filed){
                $data->{$filed} = $request->get($filed);
            }
            $data->save();           
            DB::commit();
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = "更新成功!";     
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }
        echo json_encode($records);
    }
    
    
}