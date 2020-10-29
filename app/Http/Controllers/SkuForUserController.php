<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\SkuForUser;
use App\SkuForUserLog;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class SkuForUserController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function upload( Request $request )
    {
        if(!Auth::user()->can(['skuforuser-import'])) die('Permission denied -- skuforuser-import');
        if($request->isMethod('POST')){
            $file = $request->file('importFile');
            if($file){
                if($file->isValid()){

                    $originalName = $file->getClientOriginalName();
                    $ext = $file->getClientOriginalExtension();
                    $type = $file->getClientMimeType();
                    $realPath = $file->getRealPath();
                    $newname = date('Y-m-d-H-i-S').'-'.uniqid().'.'.$ext;
                    $newpath = '/uploads/skuforuserUpload/'.date('Ymd').'/';
                    $inputFileName = public_path().$newpath.$newname;
                    $bool = $file->move(public_path().$newpath,$newname);

                    if($bool){
                        $users_data = User::where('locked',0)->pluck('id','name');
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
                        $importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                        $successCount=0;
                        foreach($importData as $key => $data){
                            $sku = trim(array_get($data,'A'));
                            $site = trim(array_get($data,'B'));
                            if($key==1){
                                if($sku!='sku' || $site!='site'){
                                    die('import template error');
                                }
                            }
                            if($key>1 && $sku && $site){
                                $marketplace_id = array_get(siteToMarketplaceid(),strtolower($site));
                                $last_data = SkuForUser::where('sku',$sku)->where('marketplace_id',$marketplace_id)->where('date',date('Y-m-d'))->get(['producter','planer','dqe','te'])->first();
                                $last_data = empty($last_data)?[]:$last_data->toArray();    
                                $new_data = [
                                    'producter'=> intval(array_get($users_data,trim(array_get($data,'C')))),
                                    'planer'=> intval(array_get($users_data,trim(array_get($data,'D')))),
                                    'dqe'=> intval(array_get($users_data,trim(array_get($data,'E')))),
                                    'te'=> intval(array_get($users_data,trim(array_get($data,'F'))))
                                ];
                                if($last_data!=$new_data){
                                    $new_data['created_user_id'] = Auth::user()->id;
                                    $successCount++;
                                    SkuForUserLog::updateOrCreate(
                                        [
                                            'sku'=>$sku,
                                            'marketplace_id'=>$marketplace_id,
                                            'status'=>0
                                        ],
                                        $new_data
                                    );    
                                }
                            }
                        }
                        $request->session()->flash('success_message','Import Success! '.$successCount);
                    }else{
                        $request->session()->flash('error_message','Upload Failed');
                    }
                }
            }else{
                $request->session()->flash('error_message','Please Select Upload File');
            }
        }
        return redirect('skuforuser');

    }


    public function export(Request $request){
        set_time_limit(0);
        if(!Auth::user()->can(['skuforuser-export'])) die('Permission denied -- skuforuser-export');
        $curr_date = date('Y-m-d');

        $date = array_get($_REQUEST,'date')??$curr_date;

        if($date>=$curr_date){
            $datas = DB::connection('amazon')->table(DB::raw("(select * from sku_for_user where date = '$curr_date') as sku_for_user"))
            ->select('sku_for_user.*','new_producter','new_planer','new_dqe','new_te','confirm_id')
            ->leftJoin(DB::raw('(select id as confirm_id,sku,marketplace_id,producter as new_producter,planer as new_planer,dqe as new_dqe,te as new_te from sku_for_user_logs where status = 0) as new_data'),function($q){
                $q->on('sku_for_user.sku', '=', 'new_data.sku')->on('sku_for_user.marketplace_id', '=', 'new_data.marketplace_id');
            });
        }else{
            $datas = SkuForUser::where('date',$date)->selectRaw('*,0 as confirm_id');
        }

        $exportFileName = '';
        $users_data = User::where('locked',0)->pluck('name','id');
        $users_data[0]='N/A';
        if(array_get($_REQUEST,'sku')){
            $datas = $datas->whereIn('sku_for_user.sku',explode(',',str_replace([' ','	'],'',array_get($_REQUEST,'sku'))));
            $exportFileName.=str_replace(' ','',array_get($_REQUEST,'sku')).'_';
        }
        if(array_get($_REQUEST,'date')){
            $datas = $datas->where('date',array_get($_REQUEST,'date'));
            $exportFileName.=array_get($_REQUEST,'date').'_';
        }
        if(array_get($_REQUEST,'status')!==NULL && array_get($_REQUEST,'status')!==''){
            $datas = $datas->whereIn('status',explode(',',array_get($_REQUEST,'status')));
            $addFileName=[];
            foreach(explode(',',array_get($_REQUEST,'status')) as $val){
                $addFileName[] = array_get(getSkuStatuses(),$val);
            }
            $exportFileName.=implode(',',$addFileName).'_';
        }
        if(array_get($_REQUEST,'producter')){
            $datas = $datas->whereIn('producter',explode(',',array_get($_REQUEST,'producter')));
            $addFileName=[];
            foreach(explode(',',array_get($_REQUEST,'producter')) as $val){
                $addFileName[] = array_get($users_data,$val);
            }
            $exportFileName.=implode(',',$addFileName).'_';
        }
        if(array_get($_REQUEST,'planer')){
            $datas = $datas->whereIn('planer',explode(',',array_get($_REQUEST,'planer')));
            $addFileName=[];
            foreach(explode(',',array_get($_REQUEST,'planer')) as $val){
                $addFileName[] = array_get($users_data,$val);
            }
            $exportFileName.=implode(',',$addFileName).'_';
        }
        if(array_get($_REQUEST,'dqe')){
            $datas = $datas->whereIn('dqe',explode(',',array_get($_REQUEST,'dqe')));
            $addFileName=[];
            foreach(explode(',',array_get($_REQUEST,'dqe')) as $val){
                $addFileName[] = array_get($users_data,$val);
            }
            $exportFileName.=implode(',',$addFileName).'_';
        }
        if(array_get($_REQUEST,'te')){
            $datas = $datas->whereIn('te',explode(',',array_get($_REQUEST,'te')));
            $addFileName=[];
            foreach(explode(',',array_get($_REQUEST,'te')) as $val){
                $addFileName[] = array_get($users_data,$val);
            }
            $exportFileName.=implode(',',$addFileName).'_';
        }

        if(array_get($_REQUEST,'limit')){
            $datas->offset(intval(array_get($_REQUEST,'offset')))->limit(intval(array_get($_REQUEST,'limit')));
            $exportFileName.='Page'.intval(intval(array_get($_REQUEST,'offset'))/intval(array_get($_REQUEST,'limit'))+1).'_';
        }

        if(!$exportFileName) $exportFileName = 'All_';
        $exportFileName.=date('YmdHis').'.xlsx';
        
        if(!Auth::user()->can(['skuforuser-show-all'])){
            $user_ids = [];
            $user_ids[] = Auth::user()->id;
            $datas = $datas->where(function ($query) use ($user_ids) {
                $query->whereIn('producter', $user_ids)
                        ->orwhereIn('planer', $user_ids)
						 ->orwhereIn('dqe', $user_ids)
						  ->orwhereIn('te', $user_ids);
            });
        }
        $datas =  $datas->orderBy('confirm_id','desc')->orderBy('id','asc')->get()->toArray();
        $datas = json_decode(json_encode($datas), true);
        $arrayData = array();
        $arrayData[] = [
            'sku','site','producter','planer','dqe','te','description','status'
        ];
        foreach ( $datas as $data){
            $arrayData[] = array(
                $data['sku'],
                array_get(getSiteUrl(),$data['marketplace_id']),
                array_get($users_data,$data['producter']),
                array_get($users_data,$data['planer']),
                array_get($users_data,$data['dqe']),
                array_get($users_data,$data['te']),
                $data['description'],
                array_get(getSkuStatuses(),$data['status']),    
            );
        }

        if($arrayData){
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()->fromArray($arrayData,NULL,'A1');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$exportFileName.'"');
            header('Cache-Control: max-age=0');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }


    public function index()
    {
        if(!Auth::user()->can(['skuforuser-show'])) die('Permission denied -- skuforuser-show');
        $date=date('Y-m-d');
        $users_data = User::where('locked',0)->pluck('name','id');
        
        return view('skuforuser/index',['date'=>$date ,'users'=>$users_data,'status'=>SkuForUserLog::STATUS]);

    }

    public function get()
    {
        set_time_limit(0);
        $curr_date = date('Y-m-d');
        if (isset($_REQUEST["customActionType"])) {
            if(!Auth::user()->can(['skuforuser-batch-update'])) die('Permission denied -- skuforuser-batch-update');
            $updateData=array();
            if($_REQUEST["customActionType"] == "group_action"){
                if(array_get($_REQUEST,"confirmStatus")){
                    $updateData['status'] = array_get($_REQUEST,"confirmStatus");
                    $updateData['updated_user_id'] = Auth::user()->id;
                }
                if($updateData['status'] == 1){
                    foreach($_REQUEST["id"] as $log_id){
                        $skuForUserLog = SkuForUserLog::where('status',0)->find($log_id);
                        SkuForUser::where('sku',$skuForUserLog->sku)->where('marketplace_id',$skuForUserLog->marketplace_id)->where('date',$curr_date)
                        ->update(
                            array(
                                'producter'=>$skuForUserLog->producter,
                                'planer'=>$skuForUserLog->planer,
                                'dqe'=>$skuForUserLog->dqe,
                                'te'=>$skuForUserLog->te,
                            )
                        );
                        $skuForUserLog->status = $updateData['status'];
                        $skuForUserLog->updated_user_id = $updateData['updated_user_id'];
                        $skuForUserLog->save();
                    }
                }else{
                    if($updateData) SkuForUserLog::whereIn('id',$_REQUEST["id"])->update($updateData);
                }
                
                unset($updateData);      
            }
        }

        $date = array_get($_REQUEST,'date')??$curr_date;

        if($date>=$curr_date){
            $datas = DB::connection('amazon')->table(DB::raw("(select * from sku_for_user where date = '$curr_date') as sku_for_user"))
            ->select('sku_for_user.*','new_producter','new_planer','new_dqe','new_te','confirm_id')
            ->leftJoin(DB::raw('(select id as confirm_id,sku,marketplace_id,producter as new_producter,planer as new_planer,dqe as new_dqe,te as new_te from sku_for_user_logs where status = 0) as new_data'),function($q){
                $q->on('sku_for_user.sku', '=', 'new_data.sku')->on('sku_for_user.marketplace_id', '=', 'new_data.marketplace_id');
            });
        }else{
            $datas = SkuForUser::where('date',$date)->selectRaw('*,0 as confirm_id');
        }

        $users_data = User::where('locked',0)->pluck('name','id');
        $users_data[0]='N/A';
        if(array_get($_REQUEST,'sku')){
            $datas = $datas->whereIn('sku_for_user.sku',explode(',',str_replace([' ','	'],'',array_get($_REQUEST,'sku'))));
        } 
        if(array_get($_REQUEST,'status')!==NULL && array_get($_REQUEST,'status')!==''){
            $datas = $datas->whereIn('status',array_get($_REQUEST,'status'));
        }
        if(array_get($_REQUEST,'producter')){
            $datas = $datas->whereIn('producter',array_get($_REQUEST,'producter'));
        }
        if(array_get($_REQUEST,'planer')){
            $datas = $datas->whereIn('planer',array_get($_REQUEST,'planer'));
        }
        if(array_get($_REQUEST,'dqe')){
            $datas = $datas->whereIn('dqe',array_get($_REQUEST,'dqe'));
        }
        if(array_get($_REQUEST,'te')){
            $datas = $datas->whereIn('te',array_get($_REQUEST,'te'));
        }

        if(!Auth::user()->can(['skuforuser-show-all'])){
            $user_ids = [];
            $user_ids[] = Auth::user()->id;
            $datas = $datas->where(function ($query) use ($user_ids) {
                $query->whereIn('producter', $user_ids)
                        ->orwhereIn('planer', $user_ids)
						 ->orwhereIn('dqe', $user_ids)
						  ->orwhereIn('te', $user_ids);
            });
        }
        
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        
        $lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->orderBy('confirm_id','desc')->orderBy('id','asc')->get()->toArray();
        $lists = json_decode(json_encode($lists), true);
        $records = array();
        $records["data"] = array();

		foreach ( $lists as $list){
            $iDisplayStart++;
            $records["data"][] = array(
                array_get($list,'confirm_id')?'<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$list['confirm_id'].'"  /><span></span></label>':'',
                $iDisplayStart,
                array_get(getSiteUrl(),$list['marketplace_id']),
                $list['sku'],
				$list['description'],
				array_get(getSkuStatuses(),$list['status']),
                (array_get($list,'new_producter')?('<span class="badge" style="text-decoration: line-through;">'.array_get($users_data,$list['producter']).'</span><span class="badge badge-danger">'.array_get($users_data,$list['new_producter']).'</span>'):array_get($users_data,$list['producter'])),
                (array_get($list,'new_planer')?('<span class="badge" style="text-decoration: line-through;">'.array_get($users_data,$list['planer']).'</span><span class="badge badge-danger">'.array_get($users_data,$list['new_planer']).'</span>'):array_get($users_data,$list['planer'])),
                (array_get($list,'new_dqe')?('<span class="badge" style="text-decoration: line-through;">'.array_get($users_data,$list['dqe']).'</span><span class="badge badge-danger">'.array_get($users_data,$list['new_dqe']).'</span>'):array_get($users_data,$list['dqe'])),
                (array_get($list,'new_te')?('<span class="badge" style="text-decoration: line-through;">'.array_get($users_data,$list['te']).'</span><span class="badge badge-danger">'.array_get($users_data,$list['new_te']).'</span>'):array_get($users_data,$list['te'])),
            );
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);

    }
}