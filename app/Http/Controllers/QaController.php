<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Qa;
use Illuminate\Support\Facades\Session;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
class QaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     *
     */

    public function __construct()
    {

    }
	
	public function getUsers(){
        $users = User::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {	
		$keywords = $request->get('keywords');
		if($keywords){
			$qas = Qa::where('product','like','%'.$keywords.'%')->orWhere('product_line','like','%'.$keywords.'%')->orWhere('item_no','like','%'.$keywords.'%')->orWhere('model','like','%'.$keywords.'%')->orWhere('title','like','%'.$keywords.'%')->orWhere('description','like','%'.$keywords.'%')->orderBy('created_at','desc')->paginate(8);
		}else{
			$qas = Qa::orderBy('created_at','desc')->paginate(8);
		}

        return view('qa/index',['qas'=>$qas,'keywords'=>$keywords,'users'=>$this->getUsers()]);

    }
	
	public function show($id)
    {

        $qa = Qa::where('id',$id)->first();

        return view('qa/view',['qa'=>$qa,'users'=>$this->getUsers()]);
    }
	

    public function update(Request $request,$id)
    {

        $seller_account = Qa::findOrFail($id);
        $seller_account->dqe_content = $request->get('dqe_content');
        if ($seller_account->save()) {
            $request->session()->flash('success_message','Save Question Success');
            return redirect('question/'.$id);
        } else {
            $request->session()->flash('error_message','Save Question Failed');
            return redirect()->back()->withInput();
        }
    }



}