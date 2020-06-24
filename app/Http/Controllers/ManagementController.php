<?php

namespace App\Http\Controllers;

class managementController extends Controller
{
	
	public function __construct()
	{

		$this->middleware('auth');
	parent::__construct();
	}
	
    public function index()
    {
        return view('management.index');
    }


}