<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\PpcProfile;
use App\Models\PpcSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use DB;

class PpcscheduleController extends Controller
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
        if(!Auth::user()->can(['adv-show'])) die('Permission denied -- adv-show');
        $profiles = PpcProfile::get();
        return view('ppcschedule/index',['profiles'=>$profiles]);
    }

    public function listSchedules(Request $request)
    {
        $datas = PpcSchedule::with('user')->with('campaign')->with('profile');
        if($request->get('profile_id')) $datas = $datas->whereIn('profile_id',$request->get('profile_id'));
        if($request->get('ad_type')) $datas = $datas->where('ad_type',$request->get('ad_type'));	
		$keyword = array_get($_REQUEST,'record_name');
        if($keyword){
            $datas = $datas->whereHas('campaign', function ($query) use ($keyword) {
    			$query->where('name', 'like', '%'.$keyword.'%');
			});
        }
		
		if($request->get('user_id')) $datas = $datas->whereIn('user_id',$request->get('user_id'));
		
        if(array_get($_REQUEST,'status')!==NULL && array_get($_REQUEST,'status')!==''){
            $datas = $datas->where('status',array_get($_REQUEST,'status'));
        }
        if(array_get($_REQUEST,'record_type')){
            $datas = $datas->where('record_type',array_get($_REQUEST,'record_type'));
        }
        
        
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        
        $records["data"] = array();
		foreach ( $lists as $list){
            $record_name = "";
            $record_data = DB::table('ppc_'.strtolower($request->get('ad_type')).'_'.unCamelize($list['record_type']).'s')->where(unCamelize($list['record_type']).'_id',$list['record_type_id'])->get()->toArray();
            if(!empty($record_data)){
                $record_data = $record_data[0];
                if($list['record_type']=='target'){
                    $data = json_decode($record_data->resolved_expression,true);
                    $record_name = $data[0]['type'].' - '.$data[0]['value'];
                }elseif($list['record_type']=='keyword'){
                    $record_name = $record_data->match_type.' - '.$record_data->keyword_text;
                }elseif($list['record_type']=='ad'){
                    $record_name = $record_data->asin.' - '.$record_data->sku;
                }else{
                    $record_name = $record_data->name;
                }
            }
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
				array_get($list,'profile.account_name'),
                array_get($list,'campaign.name'),
				$list['record_type'],
                $record_name??$list['record_name'],
                array_get(\App\Models\PpcSchedule::STATUS,$list['status']),
                $list['date_from'].'<BR>'.$list['date_to'],
                $list['time'],
                'State : '.$list['state'].'<BR>Bid : '.$list['bid'],
                $list['done_at'].' '.$list['message'],
               
                array_get($list,'user.name').'<BR>'.$list['updated_at'],
            );
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function editSchedule(Request $request)
    {
        $form=[
            'profile_id'=>$request->get('profile_id'),
            'ad_type'=>$request->get('ad_type'),
            'campaign_id'=>$request->get('campaign_id'),
            'record_type'=>$request->get('record_type'),
            'record_type_id'=>$request->get('record_type_id'),
            'record_name'=>$request->get('record_name'),
            'status'=>1,
            'date_from'=>date('Y-m-d'),
            'date_to'=>date('Y-m-d'),
            'time'=>'00:00',
            'state'=>'enabled',
            'bid'=>$request->get('bid'),
        ];
        if($request->get('id')) $form =  PpcSchedule::where('id',$request->get('id'))->first()->toArray();
        return view('adv/schedule_edit',['form'=>$form]);
    }
	
    public function saveSchedule(Request $request)
    {
        DB::beginTransaction();
        try{ 
            $id = intval($request->get('id'));
			$schedules = $request->input('schedules')??[];
			if(!empty($schedules)){
				foreach($schedules as $schedule){
					PpcSchedule::create(
					[
						'profile_id'=>$request->get('profile_id'),
						'ad_type'=>$request->get('ad_type'),
						'campaign_id'=>$request->get('campaign_id'),
						'record_type'=>$request->get('record_type'),
						//'record_name'=>$request->get('record_name'),
						'record_type_id'=>$request->get('record_type_id'),
						'status'=>$request->get('status'),
						'date_from'=>$request->get('date_from'),
						'date_to'=>$request->get('date_to'),
						'time'=>array_get($schedule,'time'),
						'state'=>array_get($schedule,'state'),
						'bid'=>array_get($schedule,'bid'),
					]
					);
				}
			}else{
				$data = $id?(PpcSchedule::findOrFail($id)):(new PpcSchedule);
				$fileds = array(
					'profile_id',
					'ad_type',
					'campaign_id',
					'record_type',
					//'record_name',
					'record_type_id',
					'status',
					'date_from',
					'date_to',
					'time',
					'state',
					'bid'
				);
				foreach($fileds as $filed){
					$data->{$filed} = $request->get($filed);
				}
				if($id && (date('Gi')<intval(str_replace(':','',$request->get('time')))) && ($request->get('status')==1)){
					$data->done_at = $data->message = NULL;
				}
				$data->user_id = Auth::user()->id;
				$data->save();
			}
            DB::commit();
            $records["code"] = 'SUCCESS';
            $records["description"] = "更新成功!";
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["code"] = 'FAILED';
            $records["description"] = $e->getMessage();
        }
        echo json_encode($records);
    }

    

    public function scheduleBatchUpdate(Request $request){
        $status = $request->get('confirmStatus');
        $ids = $request->get('id');
        try{
            PpcSchedule::whereIn('id',$ids)->update(['status'=>$status]);
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = 'Success';     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);   

    }


    public function batchScheduled(Request $request)
    {
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $campaign_id = $request->get('campaign_id');
        $record_type = $request->get('record_type');
        $ids = $request->get('ids');
        return view('adv/schedule_batch',['profile_id'=>$profile_id,'ad_type'=>$ad_type,'campaign_id'=>$campaign_id,'record_type'=>$record_type,'ids'=>$ids]);
    }
	
    public function batchSaveScheduled(Request $request)
    {
        DB::beginTransaction();
        try{ 
            $ids = $request->get('ids');
            if($ids){
                $ids = explode(',',$ids);
                foreach($ids as $id){
                    $data = new PpcSchedule;
                    $_id = explode('-',$id);
                    if(count($_id)>1){
                        $data->ad_type = $_id[0];
                        $data->campaign_id = $id = $_id[1];
                    }else{
                        $data->ad_type = $request->get('ad_type');
                        $data->campaign_id = $request->get('campaign_id');
                    }
                    if($data->ad_type!='SProducts') continue;
                    $fileds = array(
                        'profile_id',
                        'record_type',
                        'date_from',
                        'date_to',
                        'time',
                        'state',
                        'bid'
                    );
                    foreach($fileds as $filed){
                        $data->{$filed} = $request->get($filed);
                    }
                    $data->record_type_id = $id;
                    $data->status = 1;
                    $data->user_id = Auth::user()->id;
                    if($data->bid<=0) continue;
                    $data->save();
                }
            }
            DB::commit();
            $records["code"] = 'SUCCESS';
            $records["description"] = "更新成功!";
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["code"] = 'FAILED';
            $records["description"] = $e->getMessage();
        }
        echo json_encode($records);

    }
}