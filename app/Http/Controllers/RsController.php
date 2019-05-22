<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rs;
use Illuminate\Support\Facades\Session;
use App\Accounts;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PDO;
use DB;
use Log;
class RsController extends Controller
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
        if(!Auth::user()->can(['review-tab-show'])) die('Permission denied -- review-tab-show');
        $rss = Rs::get()->toArray();
        return view('rs/index',['rss'=>$rss]);

    }

    public function create()
    {
        if(!Auth::user()->can(['review-tab-create'])) die('Permission denied -- review-tab-create');
        return view('rs/add');
    }


    public function store(Request $request)
    {
        if(!Auth::user()->can(['review-tab-create'])) die('Permission denied -- review-tab-create');
		
        $this->validate($request, [
			'title' => 'required|string',
			'content' => 'required|string',
        ]);
		
        $rule = new Rs;
		
		$rule->title = $request->get('title');
		$rule->content = $request->get('content');
        if ($rule->save()) {
            $request->session()->flash('success_message','Set Step Success');
            return redirect('rs');
        } else {
            $request->session()->flash('error_message','Set Step Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
        if(!Auth::user()->can(['review-tab-delete'])) die('Permission denied -- review-tab-delete');
		if($result){
			$request->session()->flash('success_message','Delete Step Success');
		}else{
			$request->session()->flash('error_message','Delete Step Failed');
		}
        return redirect('rs');
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->can(['review-tab-show'])) die('Permission denied -- review-tab-show');
        $rules= Rs::where('id',$id)->first()->toArray();
        if(!$rules){
            $request->session()->flash('error_message','Step not Exists');
            return redirect('rs');
        }
        return view('rs/edit',['rs'=>$rules]);
    }

    public function update(Request $request,$id)
    {
        if(!Auth::user()->can(['review-tab-update'])) die('Permission denied -- review-tab-update');
		
        $this->validate($request, [
			'title' => 'required|string',
			'content' => 'required|string',
        ]);
        
        $rule =  Rs::findOrFail($id);
		$rule->title = $request->get('title');
		$rule->content = $request->get('content');
        if ($rule->save()) {
            $request->session()->flash('success_message','Set Step Success');
            return redirect('rs');
        } else {
            $request->session()->flash('error_message','Set Step Failed');
            return redirect()->back()->withInput();
        }
		
    }


}