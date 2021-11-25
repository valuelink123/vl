<?php

namespace App\Http\Controllers;
use Log;
class CuckooController extends Controller
{


    /**
     * CuckooController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function index(){
		$input = file_get_contents("php://input");
		Log::info('cuckoo-data:'.(isset($input)?$input:""));
		return 'ok';
    }

    public function feedback(){

    }

}