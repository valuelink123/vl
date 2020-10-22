<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Models\TransferTask;
use App\Models\TransferPlan;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Illuminate\Http\Response;
class TransferPlanController extends Controller
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
        //if(!Auth::user()->can(['transfer-plan-show'])) die('Permission denied -- transfer-plan-show');
        return view('transfer/planList',['sellers'=>getUsers('sap_seller'), 'users'=>getUsers(), 'status'=>TransferPlan::STATUS]);

    }

    public function get(Request $request)
    {
        if (isset($_REQUEST["customActionType"])) {
            //if(!Auth::user()->can(['transfer-plan-batch-update'])) die('Permission denied -- transfer-plan-batch-update');
            $updateData=array();
            if($_REQUEST["customActionType"] == "group_action"){
                $updateData['status'] = intval(array_get($_REQUEST,"confirmStatus"));
                DB::beginTransaction();
                try{ 
                    if($updateData) TransferPlan::whereIn('id',$_REQUEST["id"])->where('status','<>',1)->update($updateData);
                    if($updateData['status'] == 1) $transferTaskKey = uniqid('Task');
                    foreach($_REQUEST["id"] as $plan_id){
                        $transferPlan = TransferPlan::where('status',$updateData['status'])->find($plan_id);
                        if(empty($transferPlan)) continue;
                        if($updateData['status'] == 1){
                            $status = 3;
                            if($transferPlan->require_rebrand) $status=2;
                            if($transferPlan->require_purchase) $status=1;
                            if($transferPlan->require_attach) $status=0;
                            $result = TransferTask::firstOrCreate(
                                [
                                    'transfer_plan_id'=>$transferPlan->id
                                ],
                                [
                                    'transfer_task_key'=>$transferTaskKey,
                                    'status'=>$status,
                                    'user_id'=>Auth::user()->id
                                ]
                            );
                            if($result->wasRecentlyCreated) SaveOperationLog('transfer_tasks', $result->id, ['status'=>$status]);
                        }
                        SaveOperationLog('transfer_plans', $transferPlan->id, $updateData);
                    }
                    DB::commit(); 
                    $request->session()->flash('success_message','Update Success');
                }catch (\Exception $e) { 
                    $request->session()->flash('error_message',$e->getMessage());
                    DB::rollBack(); 
                }    
                unset($updateData);      
            }
        }

        $datas = TransferPlan::select('transfer_plans.*','transfer_requests.marketplace_id','transfer_requests.bg','transfer_requests.bu','transfer_requests.asin','transfer_requests.sku'
        ,'transfer_requests.quantity as request_quantity','transfer_tasks.transfer_task_key','transfer_tasks.status as task_status','transfer_tasks.carrier_code as task_carrier_code'
        ,'transfer_tasks.ship_method as task_ship_method','transfer_tasks.tracking_number','transfer_tasks.out_date as task_out_date','transfer_tasks.in_date as task_in_date')
        ->leftJoin('transfer_requests',function($q){
            $q->on('transfer_plans.transfer_request_id', '=', 'transfer_requests.id');
        })
        ->leftjoin('transfer_tasks',function($q){
            $q->on('transfer_plans.id', '=', 'transfer_tasks.transfer_plan_id');
        });
        
        if(array_get($_REQUEST,'marketplace_id')){
            $datas = $datas->whereIn('transfer_requests.marketplace_id',array_get($_REQUEST,'marketplace_id'));
        }
        if(array_get($_REQUEST,'bg')){
            $datas = $datas->whereIn('transfer_requests.bg',array_get($_REQUEST,'bg'));
        }
        if(array_get($_REQUEST,'bu')){
            $datas = $datas->whereIn('transfer_requests.bu',array_get($_REQUEST,'bu'));
        }
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
            $datas = $datas->whereIn('transfer_plans.status',array_get($_REQUEST,'status'));
        }
        
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->orderBy('transfer_plans.id','desc')->get()->toArray();
        $records = array();
        $records["data"] = array();

		foreach ( $lists as $list){
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
                array_get(array_flip(getSiteCode()),$list['marketplace_id']),
                $list['bg'],
                $list['bu'],
                $list['out_factory'],
                $list['in_factory'],
                $list['asin'],
                $list['sku'],
                ($list['status']!==NUll)?array_get(TransferPlan::STATUS,$list['status']):'',
                $list['request_quantity'],
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
                $list['task_carrier_code'].($list['task_ship_method']?'</BR>'.$list['task_ship_method']:'').($list['tracking_number']?'</BR>'.$list['tracking_number']:''),
                $list['task_out_date'],
				$list['task_in_date'],
            );
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }
}