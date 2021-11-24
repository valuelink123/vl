<?php


namespace App\Http\Controllers;


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
        $type = $_REQUEST['type'];
        $data = $_REQUEST['data'];
        Log::info('type:',$type);
        Log::info('data:',$data);
        return 'ok';
    }

    public function feedback(){

    }

}