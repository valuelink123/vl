<?php

namespace App\Http\Controllers;

class ManageDistributeTimeController extends Controller
{
	
	public function __construct()
	{

		$this->middleware('auth');
		parent::__construct();
	}
	public function safetyStockDays()
    {
   		return view('ManageDistributeTime.safetyStockDays');
    }
	public function fba()
	{
		return view('ManageDistributeTime.fba');
	}
	public function fbm()
	{
		return view('ManageDistributeTime.fbm');
	}
	public function internationalTransportTime()
	{
		return view('ManageDistributeTime.internationalTransportTime');
	}
}
 