<?php


namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

class MarketingPlanController extends Controller
{
	
	public function __construct()
	{

		$this->middleware('auth');
	parent::__construct();
	}
	
    public function index()
    {
		$user =Auth::user()->toArray();
		$sap_seller_id=$user['sap_seller_id'];
		return view('marketingPlan.index',['sap_seller_id'=>$sap_seller_id]);
    }
	public function detail()
	{
		$user =Auth::user()->toArray();
		$sap_seller_id=$user['sap_seller_id'];
	    return view('marketingPlan.detail',['sap_seller_id'=>$sap_seller_id]);
	}
}