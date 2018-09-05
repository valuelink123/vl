<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Auto;
use Illuminate\Support\Facades\Session;
use App\Accounts;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
class AutoController extends Controller
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
        $autos = Auto::get()->toArray();
        $users_array = $this->getUsers();
        return view('auto/index',['rules'=>$autos,'users'=>$users_array]);

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
        return view('auto/add',['users'=>$this->getUsers(),'accounts'=>$this->getAccounts()]);
    }


    public function store(Request $request)
    {
        if(!Auth::user()->admin) die();
        $this->validate($request, [
            'priority' => 'required|int',
            'rule_name' => 'required|string',
            'content' => 'required|string',
        ]);
        $rule = new Auto;
        $rule->priority = intval($request->get('priority'));
        $rule->rule_name = $request->get('rule_name');
        $rule->subject = $request->get('subject');
        $rule->to_email = $request->get('to_email')?implode(';',$request->get('to_email')):null;
        $rule->from_email = $request->get('from_email');
        $rule->date_from = $request->get('date_from');
        $rule->date_to = $request->get('date_to');
        $rule->time_from = $request->get('time_from');
        $rule->time_to = $request->get('time_to');
        $rule->weeks = $request->get('weeks')?implode(';',$request->get('weeks')):null;
        $rule->users = $request->get('weeks')?implode(';',$request->get('user')):null;
        $rule->content = $request->get('content');
        if($request->get('id')>0){
            $rule->id = $request->get('id');
        }
        if ($rule->save()) {
            $request->session()->flash('success_message','Set Auto Reply Success');
            return redirect('auto');
        } else {
            $request->session()->flash('error_message','Set Auto Reply Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        Auto::where('id',$id)->delete();
        $request->session()->flash('success_message','Delete Auto Reply Success');
        return redirect('auto');
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        $auto= Auto::where('id',$id)->first()->toArray();
        if(!$auto){
            $request->session()->flash('error_message','Auto Reply not Exists');
            return redirect('auto');
        }
        return view('auto/edit',['rule'=>$auto,'users'=>$this->getUsers(),'accounts'=>$this->getAccounts()]);
    }

    public function update(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        $this->validate($request, [
            'priority' => 'required|int',
            'rule_name' => 'required|string',
            'content' => 'required|string',
        ]);

        $rule = Auto::findOrFail($id);
        $rule->priority = intval($request->get('priority'));
        $rule->rule_name = $request->get('rule_name');
        $rule->subject = $request->get('subject');
        $rule->to_email = $request->get('to_email')?implode(';',$request->get('to_email')):null;
        $rule->from_email = $request->get('from_email');
        $rule->date_from = $request->get('date_from');
        $rule->date_to = $request->get('date_to');
        $rule->time_from = $request->get('time_from');
        $rule->time_to = $request->get('time_to');
        $rule->weeks = $request->get('weeks')?implode(';',$request->get('weeks')):null;
        $rule->users = $request->get('weeks')?implode(';',$request->get('user')):null;
        $rule->content = $request->get('content');

        if ($rule->save()) {
            $request->session()->flash('success_message','Set Auto Reply Success');
            return redirect('auto');
        } else {
            $request->session()->flash('error_message','Set Auto Reply Failed');
            return redirect()->back()->withInput();
        }
    }

}