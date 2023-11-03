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
		parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		if(!Auth::user()->can(['distribution-analysis-show'])) die('Permission denied -- distribution-analysis-show');
		$where= "where seller_asins.site='www.amazon.com' and seller_asins.item_code<>'a:0:{}'";
		if (Auth::user()->seller_rules) {
			$where.= getSellerRules(Auth::user()->seller_rules,'b.bg','b.bu');
		} elseif (Auth::user()->sap_seller_id) {
			$where.= " and b.sap_seller_id=".Auth::user()->sap_seller_id;
		} else {
		
		}
		
		
		$datas=DB::select("select seller_asins.asin,seller_asins.site,seller_asins.sales,(transfer+stock) as fba_stock,fbm_stock,total_star,avg_star,profits,fba_stock_keep,item_code,profits_value 
from seller_asins left join (select asin,site,max(bg) as bg,max(bu) as bu,max(sap_seller_id) as sap_seller_id
from asin group by asin,site) as b  on seller_asins.asin=b.asin and seller_asins.site=b.site   ".$where);
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