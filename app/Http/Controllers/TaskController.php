<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use Illuminate\Http\Response;
class TaskController extends Controller
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

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		if(!Auth::user()->can(['task-show'])) die('Permission denied -- task-show');
		$date_from=date('Y-m-d',strtotime('-30 days'));	
		$date_to=date('Y-m-d');		
		$teams= DB::select('select bg,bu from asin group by bg,bu ORDER BY BG ASC,BU ASC');
        return view('task/index',['date_from'=>$date_from ,'date_to'=>$date_to,  'users'=>$this->getUsers(),'teams'=>$teams]);

    }

    public function get()
    {
		if (isset($_REQUEST["customActionType"])) {
	
			if($_REQUEST["customActionType"] == "group_action" && is_array($_REQUEST["id"])){
				Task::where('stage','<>',3)->where('response_user_id',Auth::user()->id)->whereIn('id',$_REQUEST["id"])->update(['stage'=>3,'response_date'=>date('Y-m-d H:i:s')]);
			}
        }
		
		$date_from=date('Y-m-d',strtotime('-30 days'));	
		$date_to=date('Y-m-d');		
		if(array_get($_REQUEST,'date_from')) $date_from= array_get($_REQUEST,'date_from');
		if(array_get($_REQUEST,'date_to')) $date_to= array_get($_REQUEST,'date_to');
		$customers = Task::select('tasks.*','users.ubg','users.ubu')->where('complete_date','<=',$date_to)->where('complete_date','>=',$date_from)
			->leftJoin('users',function($q){
				$q->on('tasks.response_user_id', '=', 'users.id');
			});
		
		
		
		if(!Auth::user()->can('rsgrequests-show-all')) {
			
		}
		
		if(!Auth::user()->can(['task-show-all'])){ 
		
			if (Auth::user()->seller_rules) {
				$rules = explode("-", Auth::user()->seller_rules);
				if (array_get($rules, 0) != '*') $customers = $customers->where('users.ubg', array_get($rules, 0));
				if (array_get($rules, 1) != '*') $customers = $customers->where('users.ubu', array_get($rules, 1));
			} elseif (Auth::user()->sap_seller_id) {
				$user_id = $this->getUserId();
				$customers = $customers->where(function ($query) use ($user_id) {
					$query->where('request_user_id', $user_id)
						  ->orwhere('response_user_id', $user_id);
				
				});
			} else {
			
			}
				
        }
		
		if(array_get($_REQUEST,'type')){
			$customers = $customers->where('type',$_REQUEST['type']);
		}
		if(isset($_REQUEST['stage']) && $_REQUEST['stage']!=''){
			$customers = $customers->where('stage',$_REQUEST['stage']);
		}
		
		
		if(Auth::user()->can(['task-show-all'])){
			if(array_get($_REQUEST,'response_user_id')){
				$customers = $customers->whereIn('response_user_id',$_REQUEST['response_user_id']);
			}
			
			if(array_get($_REQUEST,'request_user_id')){
				$customers = $customers->whereIn('request_user_id',$_REQUEST['request_user_id']);
			}
			
			if(array_get($_REQUEST,'bgbu')){
			   $bgbu = array_get($_REQUEST,'bgbu');
			   $bgbu_arr = explode('_',$bgbu);
			   if(array_get($bgbu_arr,0)) $customers = $customers->where('users.ubg',array_get($bgbu_arr,0));
			   if(array_get($bgbu_arr,1)) $customers = $customers->where('users.ubu',array_get($bgbu_arr,1));
			}
		}


        if(array_get($_REQUEST,'keywords')){
            $keywords = array_get($_REQUEST,'keywords');
            $customers = $customers->where(function ($query) use ($keywords) {

                $query->where('request', 'like', '%'.$keywords.'%')
                        ->orwhere('asin', 'like', '%'.$keywords.'%')
						 ->orwhere('sku', 'like', '%'.$keywords.'%')
						  ->orwhere('customer_email', 'like', '%'.$keywords.'%');

            });
        }

		$orderby = 'request_date';
        $sort = 'desc';

				
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==1) $orderby = 'type';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'priority';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'request_user_id';
			if($_REQUEST['order'][0]['column']==5) $orderby = 'complete_date';
            if($_REQUEST['order'][0]['column']==6) $orderby = 'response_user_id';
			if($_REQUEST['order'][0]['column']==7) $orderby = 'stage';
			if($_REQUEST['order'][0]['column']==8) $orderby = 'request_date';
			if($_REQUEST['order'][0]['column']==9) $orderby = 'score';
            $sort = $_REQUEST['order'][0]['dir'];
        }
		
		
	

		$iTotalRecords = $customers->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
		$datas =  $customers->orderBy($orderby,$sort)->skip($iDisplayStart)->take($iDisplayLength)->get()->toArray();
        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;
		

		
		$users_array = $this->getUsers();
        foreach ( $datas as $data){
		
			$records["data"][] = array(
				'<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$data['id'].'"/><span></span></label>',
				array_get(getTaskTypeArr(),$data['type']),
				(($data['asin'])?'<span class="badge badge-success">'.$data['asin'].'</span>':'').$data['request'],
				$data['priority'],
				array_get($users_array,$data['request_user_id']),
				$data['complete_date'],
				array_get($users_array,$data['response_user_id']),
				array_get(getTaskStageArr(),$data['stage']),
				$data['request_date'],
				$data['score'],
				'<a data-target="#ajax" data-toggle="modal" href="'.url('task/'.$data['id'].'/edit').'" class="badge badge-success"> <span aria-hidden="true" class="icon-magnifier"></span> </a> '
			);
        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);

    }

	public function create()
    {
		if(!Auth::user()->can(['task-create'])) die('Permission denied -- task-create');
        return view('task/add',['users'=>$this->getUsers()]);
    }
	
	public function store(Request $request)
    {
        if(!Auth::user()->can(['task-create'])) die('Permission denied -- task-create');
        $this->validate($request, [
			'user_id' => 'required|array',
			'type' => 'required|int',
			'priority' => 'required|int',
			'complete_date' => 'required|string',
			'request' => 'required|string',
        ]);
		
		$user_ids = $request->get('user_id');
		$insert_data=[];
		foreach($user_ids as $user_id){
			$insert_data[]=array(
				'type'=>$request->get('type'),
				'priority'=>$request->get('priority'),
				'complete_date'=>$request->get('complete_date'),
				'request'=>$request->get('request'),
				'request_user_id'=>Auth::user()->id,
				'request_date'=>date('Y-m-d H:i:s'),
				'response_user_id'=>$user_id,
				'asin'=>$request->get('asin'),
				'sku'=>$request->get('sku'),
				'customer_email'=>$request->get('customer_email'),
			);
		}
		
        $result = Task::insert($insert_data);
        if ($result) {
            $request->session()->flash('success_message','Create Task Success');
            return redirect('task');
        }else{
            $request->session()->flash('error_message','Create Task Failed');
            return redirect()->back()->withInput();
        }
    }
	
	
	 public function edit(Request $request,$id)
    {
        if(!Auth::user()->can(['task-show'])) die('Permission denied -- task-show');
		
		$task=  Task::findOrFail($id);
		if($task->response_user_id == Auth::user()->id  && $task->stage==0){
			$task->stage =1;
			$task->save();
		}
		$task = json_decode(json_encode( $task),true);
        return view('task/edit',['task'=>$task,'users'=>$this->getUsers()]);
    }
	

    public function update(Request $request,$id)
    {
		if(!Auth::user()->can(['task-update'])) die('Permission denied -- task-update');

        $rule = Task::findOrFail($id);
		if($rule->response_user_id == Auth::user()->id ){
			$rule->response_date = date('Y-m-d H:i:s');
			$rule->response = $request->get('response');
			$rule->stage = intval($request->get('stage'));
			$rule->asin = $request->get('asin');
			$rule->sku = $request->get('sku');
			$rule->customer_email = $request->get('customer_email');
		}
		if($rule->request_user_id == Auth::user()->id ){
			$rule->type = intval($request->get('type'));
			$rule->request = $request->get('request');
			
			$rule->priority = intval($request->get('priority'));
			$rule->response_user_id = intval($request->get('user_id'));
			$rule->complete_date = $request->get('complete_date');
			$rule->score = intval($request->get('score'));
			$rule->asin = $request->get('asin');
			$rule->sku = $request->get('sku');
			$rule->customer_email = $request->get('customer_email');
		}
		
        
        $result = $rule->save();
        echo json_encode($result);
        
    }
	
	public function destroy(Request $request,$id)
    {
        if(!Auth::user()->can(['task-delete'])) die('Permission denied -- task-delete');
        $result = Task::where('id',$id)->delete();
        echo json_encode($result);
    }
	
	public function getUsers(){
        $users = User::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }

}