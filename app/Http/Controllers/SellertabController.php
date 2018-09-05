<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sellertab;
use Illuminate\Support\Facades\Session;
use App\Accounts;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use Log;
class SellertabController extends Controller
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
        $tabs = Sellertab::get()->toArray();
        return view('sellertab/index',['tabs'=>$tabs]);

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
        return view('sellertab/add');
    }


    public function store(Request $request)
    {
        if(!Auth::user()->admin) die();
		
        $this->validate($request, [
            'tab' => 'required|string',
			'orderfield' => 'required|string',
			'order' => 'required|string',
            'tab-rules' => 'required|array',
			'show-rules' => 'required|array',
        ]);
		
        $rule = new Sellertab;
		
        $rule->tab = $request->get('tab');
		$rule->tab_rules = serialize(
			array(
				'order'=>$request->get('orderfield'),
				'by'=>$request->get('order'),
				'tabrules'=>$request->get('tab-rules'),
				'showrules'=>$request->get('show-rules'),
			)
		);
        if ($rule->save()) {
            $request->session()->flash('success_message','Set Tab Rules Success');
            return redirect('sellertab');
        } else {
            $request->session()->flash('error_message','Set Tab Rules Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
		$result = Sellertab::where('id',$id)->delete();
		if($result){
			$request->session()->flash('success_message','Delete Tab Rules Success');
		}else{
			$request->session()->flash('error_message','Delete Tab Rules Failed');
		}
        return redirect('sellertab');
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        $rules= Sellertab::where('id',$id)->first()->toArray();
        if(!$rules){
            $request->session()->flash('error_message','Tab Rules not Exists');
            return redirect('sellertab');
        }
        return view('sellertab/edit',['rules'=>$rules]);
    }

    public function update(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
		
        $this->validate($request, [
            'tab' => 'required|string',
			'orderfield' => 'required|string',
			'order' => 'required|string',
            'tab-rules' => 'required|array',
			'show-rules' => 'required|array',
        ]);
        
        $rule =  Sellertab::findOrFail($id);
		$rule->tab = $request->get('tab');
		$rule->tab_rules = serialize(
			array(
				'order'=>$request->get('orderfield'),
				'by'=>$request->get('order'),
				'tabrules'=>$request->get('tab-rules'),
				'showrules'=>$request->get('show-rules'),
			)
		);
        if ($rule->save()) {
            $request->session()->flash('success_message','Set Tab Rules Success');
            return redirect('sellertab');
        } else {
            $request->session()->flash('error_message','Set Tab Rules Failed');
            return redirect()->back()->withInput();
        }
		
    }


}