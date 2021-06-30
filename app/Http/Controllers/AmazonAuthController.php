<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class AmazonAuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {



        $profiles = $_REQUEST['profiles'];
        Log::info($profiles);
        return 'ok';

    }
}