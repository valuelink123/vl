<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rule;
use Illuminate\Support\Facades\Session;
use App\Accounts;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
class RuleController extends Controller
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
        $rules = Rule::get()->toArray();
        $users_array = $this->getUsers();
        return view('rule/index',['rules'=>$rules,'users'=>$users_array]);

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
        return view('rule/add',['users'=>$this->getUsers(),'accounts'=>$this->getAccounts()]);
    }


    public function store(Request $request)
    {
        if(!Auth::user()->admin) die();
        $this->validate($request, [
            'priority' => 'required|int',
            'rule_name' => 'required|string',
            'user_id' => 'required|int',
        ]);
        $rule = new Rule;
        $rule->priority = intval($request->get('priority'));
        $rule->rule_name = $request->get('rule_name');
        $rule->subject = $request->get('subject');
        $rule->to_email = $request->get('to_email')?implode(';',$request->get('to_email')):null;
        $rule->from_email = $request->get('from_email');
        $rule->asin = $request->get('asin');
        $rule->sku = $request->get('sku');
        $rule->timeout = $request->get('timeout');
        $rule->user_id = intval($request->get('user_id'));
        $rule->reply_status = intval($request->get('reply_status'));
        if($request->get('id')>0){
            $rule->id = $request->get('id');
        }
        if ($rule->save()) {
            $request->session()->flash('success_message','Set Rule Success');
            return redirect('rule');
        } else {
            $request->session()->flash('error_message','Set Rule Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        Rule::where('id',$id)->delete();
        $request->session()->flash('success_message','Delete Rule Success');
        return redirect('rule');
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        $rule= Rule::where('id',$id)->first()->toArray();
        if(!$rule){
            $request->session()->flash('error_message','Rule not Exists');
            return redirect('rule');
        }
        return view('rule/edit',['rule'=>$rule,'users'=>$this->getUsers(),'accounts'=>$this->getAccounts()]);
    }

    public function update(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        $this->validate($request, [
            'priority' => 'required|int',
            'rule_name' => 'required|string',
            'user_id' => 'required|int',
        ]);
        $rule = Rule::findOrFail($id);
        $rule->priority = intval($request->get('priority'));
        $rule->rule_name = $request->get('rule_name');
        $rule->subject = $request->get('subject');
        $rule->to_email = $request->get('to_email')?implode(';',$request->get('to_email')):null;
        $rule->from_email = $request->get('from_email');
        $rule->asin = $request->get('asin');
        $rule->sku = $request->get('sku');
        $rule->timeout = $request->get('timeout');
        $rule->user_id = intval($request->get('user_id'));
        $rule->reply_status = intval($request->get('reply_status'));
        if ($rule->save()) {
            $request->session()->flash('success_message','Set Rule Success');
            return redirect('rule');
        } else {
            $request->session()->flash('error_message','Set Rule Failed');
            return redirect()->back()->withInput();
        }
    }

}