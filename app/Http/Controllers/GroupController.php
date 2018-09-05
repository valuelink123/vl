<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inbox;
use App\Group;
use App\Groupdetail;
use Illuminate\Support\Facades\Session;
use App\Accounts;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use Log;
class GroupController extends Controller
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
        if(!Auth::user()->admin) die();
        $groups = Group::get()->toArray();
        $users_array = $this->getUsers();
        return view('group/index',['groups'=>$groups,'users'=>$users_array]);

    }

    public function getUsers(){
        $users = User::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }

    public function getAccounts(){
        $accounts = Accounts::get()->toArray();
        $accounts_array = array();
        foreach($accounts as $account){
            $accounts_array[$account['id']] = $account['account_email'];
        }
        return $accounts_array;
    }

    public function create()
    {
        if(!Auth::user()->admin) die();
        return view('group/add',['users'=>$this->getUsers(),'accounts'=>$this->getAccounts()]);
    }


    public function store(Request $request)
    {
        if(!Auth::user()->admin) die();

        $this->validate($request, [
            'group_name' => 'required|string',
            'group-users' => 'required|array',
        ]);
		if($this->checkAccount($request)){
            $request->session()->flash('error_message','Set Group Failed, this Group name has Already exists.');
            return redirect()->back()->withInput();
            die();
        }
        $rule = new Group;
		$users = $request->get('group-users');

		$user_ids = array();
		foreach($users as $user){
			$user_ids[] = array_get($user,'user_id');
		}
        $rule->group_name = $request->get('group_name');
		$rule->user_ids = implode(',',$user_ids);
        if($request->get('id')>0){
            $rule->id = $request->get('id');
        }
        if ($rule->save()) {
			
			$details = array();
			foreach($users as $user){
				$details[] = array('group_id'=>$rule->id,
				'user_id'=>array_get($user,'user_id'),
				'time_from'=>array_get($user,'time_from'),
				'leader'=>array_get($user,'leader'),
				'time_to'=>array_get($user,'time_to'));
			}
			if($details) DB::table('group_detail')->insert($details);
            $request->session()->flash('success_message','Set Group Success');
            return redirect('group');
        } else {
            $request->session()->flash('error_message','Set Group Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
		$existMails = Inbox::where('group_id',$id)->first();
		if($existMails){
			$request->session()->flash('error_message','Can not Delete Group , There are many mails belong this Group!');
		}else{
			Group::where('id',$id)->delete();
			Groupdetail::where('group_id',$id)->delete();
			$request->session()->flash('success_message','Delete Group Success');
		}
        return redirect('group');
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        $group= Group::where('id',$id)->first()->toArray();
		$group_details = Groupdetail::where('group_id',$id)->get()->toArray();
        if(!$group){
            $request->session()->flash('error_message','Group not Exists');
            return redirect('group');
        }
        return view('group/edit',['group'=>$group,'group_details'=>$group_details,'users'=>$this->getUsers(),'accounts'=>$this->getAccounts()]);
    }

    public function update(Request $request,$id)
    {
        if(!Auth::user()->admin) die();

        $this->validate($request, [
            'group_name' => 'required|string',
            'group-users' => 'required|array',
        ]);
		if($this->checkAccount($request)){
            $request->session()->flash('error_message','Set Group Failed, this Group name has Already exists.');
            return redirect()->back()->withInput();
            die();
        }
        $rule =  Group::findOrFail($id);
		$users = $request->get('group-users');
		$user_ids = array();
		foreach($users as $user){
			$user_ids[] = array_get($user,'user_id');
		}

		$rule->user_ids = implode(',',$user_ids);
        $rule->group_name = $request->get('group_name');

        if ($rule->save()) {

			$details = array();
			foreach($users as $user){
				$details[] = array('group_id'=>$rule->id,
				'user_id'=>array_get($user,'user_id'),
				'time_from'=>array_get($user,'time_from'),
				'leader'=>array_get($user,'leader'),
				'time_to'=>array_get($user,'time_to'));
			}
			Groupdetail::where('group_id',$id)->delete();
			if($details) DB::table('group_detail')->insert($details);
            $request->session()->flash('success_message','Set Group Success');
            return redirect('group');
        } else {
            $request->session()->flash('error_message','Set Group Failed');
            return redirect()->back()->withInput();
        }
		
    }
	
	 public function checkAccount($request){
        $id = ($request->get('id'))?($request->get('id')):0;

        $seller_account = Group::where('group_name',$request->get('group_name'))->where('id','<>',$id)
            ->first();
        if($seller_account) return true;
        return false;
    }

}