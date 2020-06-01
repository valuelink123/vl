<?php

namespace App\Http\Controllers;

class CpfrController extends Controller
{
	
	public function __construct()
	{

		$this->middleware('auth');
		parent::__construct();
	}
	
    public function index()
    {
        return view('cpfr.index');
    }
	public function allocationProgress()
	{
	    return view('cpfr.allocationProgress');
	}
	public function purchase()
	{
	    return view('cpfr.purchase');
	}
	public function barcode()
	{
	    return view('cpfr.barcode');
	}
}