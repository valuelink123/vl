<?php

namespace App\Http\Controllers;

class CollaborativeReplenishmentController extends Controller
{
	
	public function __construct()
	{

		$this->middleware('auth');
		parent::__construct();
	}
	
    public function index()
    {
        return view('collaborativeReplenishment.index');
    }
}