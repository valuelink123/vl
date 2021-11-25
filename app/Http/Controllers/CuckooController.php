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
        $abc=array(type =>"abc");
        $input = file_get_contents("php://input");
        $input_data=json_decode($input,true);
        Log::info('cuckoo-type:'.(isset($input_data["type"])?$input_data["type"]:""));
        Log::info('cuckoo-data:'.(isset($input_data["data"])?$input_data["data"]:""));
        return 'ok';
    }

    public function feedback(){

    }

}