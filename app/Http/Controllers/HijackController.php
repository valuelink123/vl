<?php

namespace App\Http\Controllers;

class HijackController extends Controller
{
	
	public function __construct()
	{

		$this->middleware('auth');
	parent::__construct();
	}
	
    public function index()
    {
        return view('hijack.index');
    }
	public function detail()
	{
	    return view('hijack.detail');
	}


}