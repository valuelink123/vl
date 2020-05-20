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
        return view('Cpfr.index');
    }
	public function allocationProgress()
	{
	    return view('Cpfr.allocationProgress');
	}
	public function purchase()
	{
	    return view('Cpfr.purchase');
	}
	public function barcode()
	{
	    return view('Cpfr.barcode');
	}
}