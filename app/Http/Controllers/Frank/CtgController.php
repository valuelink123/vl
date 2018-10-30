<?php
/**
 * Created by PhpStorm.
 * Date: 18.10.30
 * Time: 10:05
 */

namespace App\Http\Controllers\Frank;


use Illuminate\Http\Request;

class CtgController extends Controller {

    public function list(Request $req) {

        if ($req->isMethod('GET')) {
            return view('frank.ctgList');
        }

    }

    public function process(Request $req) {

        if ($req->isMethod('GET')) {
            return view('frank.ctgProcess');
        }
    }

}
