<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Groupdetail;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use log;


class TranController extends Controller
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
		$datas=DB::select("select asin,site,sales,(transfer+stock) as fba_stock,fbm_stock,total_star,avg_star,profits,fba_stock_keep,item_code,profits_value 
from seller_asins where site='www.amazon.com' and item_code<>'a:0:{}'");
		$datas = json_decode(json_encode($datas),TRUE);
		return view('tran/index',['datas'=>$datas]);
	
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