<?php

namespace App\Http\Controllers;

use App\Sendbox;
use Illuminate\Http\Request;
use App\Accounts;
use Illuminate\Support\Facades\Session;

use App\User;
use App\Inbox;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use DB;
class UserController extends Controller
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
        $users = User::Where('id','<>',env('SYSTEM_AUTO_REPLY_USER_ID',1))->get()->toArray();
        return view('user/index',['users'=>$users]);

    }

    public function getUsers(){
        $users = User::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }

    public function destroy(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        User::where('id',$id)->delete();
        $request->session()->flash('success_message','Delete User Success');
        return redirect('user');
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        $user = User::where('id',$id)->first()->toArray();
        if(!$user){
            $request->session()->flash('error_message','User not Exists');
            return redirect('user');
        }
        return view('user/edit')->with('user',$user);
    }

    public function total(Request $request)
    {
        if(!Auth::user()->admin) die();

        $date_from = array_get($_REQUEST,'date_from')?array_get($_REQUEST,'date_from'):date('Y-m-d',strtotime('-7day'));
        $date_to = array_get($_REQUEST,'date_to')?array_get($_REQUEST,'date_to'):date('Y-m-d');
        //print_r($date_from);print_r($date_to);
        $user_received_total=array();
        $user_key=array();
        $user_total_r = Inbox::select(DB::raw('count(*) as r_count, user_id,left(date,10) as date'));

        if($date_from){
            $user_total_r = $user_total_r->where('date','>=',$date_from.' 00:00:00');
        }
        if($date_to){
            $user_total_r = $user_total_r->where('date','<=',$date_to.' 23:59:59');
        }
        $user_total_r = $user_total_r->groupBy('user_id',DB::raw('left(date,10)'))->get();
        foreach($user_total_r as $r_total){
            $user_received_total[$r_total['user_id']][$r_total['date']]=$r_total['r_count'];
            $user_key[$r_total['user_id']]=1;
        }

        $user_send_total=array();
        $user_total_s = Sendbox::select(DB::raw('count(*) as s_count, user_id,left(date,10) as date'));

        if($date_from){
            $user_total_s = $user_total_s->where('date','>=',$date_from.' 00:00:00');
        }
        if($date_to){
            $user_total_s = $user_total_s->where('date','<=',$date_to.' 23:59:59');
        }

        $user_total_s = $user_total_s->groupBy('user_id',DB::raw('left(date,10)'))->get();

        foreach($user_total_s as $s_total){
            $user_send_total[$s_total['user_id']][$s_total['date']]=$s_total['s_count'];
            $user_key[$s_total['user_id']]=1;
        }


        $account_received_total=array();
        $account_key=array();
        $account_total_r = Inbox::select(DB::raw('count(*) as r_count, to_address,left(date,10) as date'));
        if($date_from){
            $account_total_r = $account_total_r->where('date','>=',$date_from.' 00:00:00');
        }
        if($date_to){
            $account_total_r = $account_total_r->where('date','<=',$date_to.' 23:59:59');
        }
        $account_total_r = $account_total_r->groupBy('to_address',DB::raw('left(date,10)'))->get();

        foreach($account_total_r as $r_total){
            $account_received_total[$r_total['to_address']][$r_total['date']]=$r_total['r_count'];
            $account_key[$r_total['to_address']]=1;
        }

        $account_send_total=array();
        $account_total_s = Sendbox::select(DB::raw('count(*) as s_count, from_address,left(date,10) as date'));
        if($date_from){
            $account_total_s = $account_total_s->where('date','>=',$date_from.' 00:00:00');
        }
        if($date_to){
            $account_total_s = $account_total_s->where('date','<=',$date_to.' 23:59:59');
        }
        $account_total_s = $account_total_s->groupBy('from_address',DB::raw('left(date,10)'))->get();

        foreach($account_total_s as $s_total){
            $account_send_total[$s_total['from_address']][$s_total['date']]=$s_total['s_count'];
            $account_key[$s_total['from_address']]=1;
        }

        return view('user/total',['date_from'=>$date_from,'date_to'=>$date_to,'user_key'=>$user_key,'account_key'=>$account_key,'user_send_total'=>$user_send_total,'user_received_total'=>$user_received_total,'account_received_total'=>$account_received_total,'account_send_total'=>$account_send_total,'users'=>$this->getUsers()]);
    }

    public function update(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        $this->validate($request, [
            'name' => 'required|string',
            'password' => 'required_with:password_confirmation|confirmed',
        ]);
        $update=array();
        $update['admin'] = ($request->get('admin'))?1:0;
        if($request->get('name')) $update['name'] = $request->get('name');
        if($request->get('password')) $update['password'] = Hash::make(($request->get('password')));
        $result = User::where('id',$id)->update($update);
        if ($result) {
            $request->session()->flash('success_message','Set User Success');
            return redirect('user');
        } else {
            $request->session()->flash('error_message','Set User Failed');
            return redirect()->back()->withInput();
        }

    }

    public function profile(Request $request){
        if ($request->getMethod()=='POST')
        {
            $this->validate($request, [
                'name' => 'required|string',
                'current_password' => 'required_with:password,password_confirmation|string',
                'password' => 'required_with:password_confirmation|confirmed',
                //'password_confirmation' => 'required_with:password|string|min:6',
            ]);
            $user = User::findOrFail(Auth::user()->id);

            $result = Hash::check($request->get('current_password'), $user->password);//Auth::validate(['password'=>$request->get('current_password')]);
            if($result){
                $user->name = $request->get('name');
                if($request->get('password')) $user->password = Hash::make(($request->get('password')));
                $user->save();
                $request->session()->flash('success_message', 'Set Profile Success');
            }else{
                $request->session()->flash('error_message','Current Password not Match');
            }

        }
        $profile = User::findOrFail(Auth::user()->id);
        return view('user/profile')->with('profile',$profile);
    }

}