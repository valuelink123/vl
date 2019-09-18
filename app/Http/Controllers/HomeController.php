<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Groupdetail;
use App\User;
use App\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
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
    public function index(Request $request)
    {
		$where= $where_star= $where_review='';
		$site = $request->get('site');
		if(Auth::user()->admin){
			$bgbu = $request->get('bgbu');
			$user_id = $request->get('user_id');
			if($bgbu){
				   $bgbu_arr = explode('_',$bgbu);
				   if(array_get($bgbu_arr,0)){
						$where_star.= "and b.bg='".array_get($bgbu_arr,0)."'";
						$where_review.= "and b.bg='".array_get($bgbu_arr,0)."'";
				   }
				   if(array_get($bgbu_arr,1)){
						$where_star.= "and b.bu='".array_get($bgbu_arr,1)."'";
						$where_review.= "and b.bu='".array_get($bgbu_arr,1)."'";
				   }
			}
		}else{
			$bgbu='';
			$user_id = Auth::user()->id;
		}
		
		$date_from = $request->get('date_from')?$request->get('date_from'):date('Y-m').'-01';
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d',strtotime('-1day'));
		if($user_id){
			$where_star.= "and b.user_id=".$user_id;
			$where.= "and b.review_user_id=".$user_id;
			$where_review.= "and a.user_id=".$user_id;
		}
		
		
		$returnDate['teams']= DB::select('select bg,bu from asin group by bg,bu ORDER BY BG ASC,BU ASC');
		$returnDate['users']= $this->getUsers();
		$returnDate['date_from']= $date_from;
		$returnDate['date_to']= $date_to;
		$returnDate['s_user_id']= $user_id;
		$returnDate['bgbu']= $bgbu;
		$returnDate['tasks']= Task::where('response_user_id',Auth::user()->id)->where('stage','<',3)->take(10)->orderBy('priority','desc')->get()->toArray();
        return view('home',$returnDate);

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