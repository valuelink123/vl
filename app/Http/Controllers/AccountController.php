<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Accounts;
use Illuminate\Support\Facades\Session;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
class AccountController extends Controller
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
        $seller_accounts = Accounts::get()->toArray();
        return view('account/index',['seller_accounts'=>$seller_accounts]);

    }

    public function create()
    {
        if(!Auth::user()->admin) die();
        return view('account/add');
    }


    public function store(Request $request)
    {
        if(!Auth::user()->admin) die();
        $this->validate($request, [
            'account_email' => 'required|email',
            'account_name' => 'required|string',
			'type' => 'required|string',
            'account_sellerid' => 'required|string',
            'email' => 'required|string',
            'password' => 'required|string',
            'imap_host' => 'required|string',
            'imap_ssl' => 'required|string',
            'imap_port' => 'required|string',
        ]);


        if($this->checkAccount($request)){
            $request->session()->flash('error_message','Set Seller Account Failed, this account has been taken in other account.');
            return redirect()->back()->withInput();
            die();
        }
        $seller_account = new Accounts;
        $seller_account->account_email = $request->get('account_email');
        $seller_account->account_name = $request->get('account_name');
        $seller_account->account_sellerid = $request->get('account_sellerid');
        $seller_account->email = $request->get('email');
        $seller_account->password = $request->get('password');
        $seller_account->imap_host = $request->get('imap_host');
        $seller_account->imap_ssl = $request->get('imap_ssl');
        $seller_account->imap_port = $request->get('imap_port');
        $seller_account->smtp_host = $request->get('smtp_host');
        $seller_account->smtp_ssl = $request->get('smtp_ssl');
        $seller_account->smtp_port = $request->get('smtp_port');
		$seller_account->type = $request->get('type');

        if($request->get('id')>0){
            $seller_account->id = $request->get('id');
        }
        if ($seller_account->save()) {
            $request->session()->flash('success_message','Set Seller Account Success');
            return redirect('account');
        } else {
            $request->session()->flash('error_message','Set Seller Account Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        Accounts::where('id',$id)->delete();
        $request->session()->flash('success_message','Delete Account Success');
        return redirect('account');
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        $seller_account = Accounts::where('id',$id)->first()->toArray();
        if(!$seller_account){
            $request->session()->flash('error_message','Account not Exists');
            return redirect('account');
        }
        return view('account/edit')->with('seller_account',$seller_account);
    }

    public function update(Request $request,$id)
    {
        if(!Auth::user()->admin) die();

        $this->validate($request, [
            'account_email' => 'required|email',
            'account_name' => 'required|string',
			'type' => 'required|string',
            'account_sellerid' => 'required|string',
            'email' => 'required|string',
            'password' => 'required|string',
            'imap_host' => 'required|string',
            'imap_ssl' => 'required|string',
            'imap_port' => 'required|string',
        ]);
        if($this->checkAccount($request)){
            $request->session()->flash('error_message','Set Seller Account Failed, this account has been taken in other account.');
            return redirect()->back()->withInput();
            die();
        }
        $seller_account = Accounts::findOrFail($id);
        $seller_account->account_email = $request->get('account_email');
        $seller_account->account_sellerid = $request->get('account_sellerid');
        $seller_account->account_name = $request->get('account_name');
        $seller_account->email = $request->get('email');
        $seller_account->password = $request->get('password');
        $seller_account->imap_host = $request->get('imap_host');
        $seller_account->imap_ssl = $request->get('imap_ssl');
        $seller_account->imap_port = $request->get('imap_port');
        $seller_account->smtp_host = $request->get('smtp_host');
        $seller_account->smtp_ssl = $request->get('smtp_ssl');
        $seller_account->smtp_port = $request->get('smtp_port');
		$seller_account->type = $request->get('type');
		
        if ($seller_account->save()) {
            $request->session()->flash('success_message','Set Seller Account Success');
            return redirect('account');
        } else {
            $request->session()->flash('error_message','Set Seller Account Failed');
            return redirect()->back()->withInput();
        }
    }

    public function checkAccount($request){
        $id = ($request->get('id'))?($request->get('id')):0;

        $seller_account = Accounts::where('account_sellerid',$request->get('account_sellerid'))->where('id','<>',$id)
            ->first();
        if($seller_account) return true;
        return false;
    }


}