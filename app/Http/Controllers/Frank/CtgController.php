<?php
/**
 * Created by PhpStorm.
 * Date: 18.10.30
 * Time: 10:05
 */

namespace App\Http\Controllers\Frank;


use App\Exceptions\HypocriteException;
use App\Models\Ctg;
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

    /**
     * @throws HypocriteException
     */
    public function import(Request $req) {

        try {
            Ctg::create($req->all());
        } catch (\Exception $e) {
            HypocriteException::wrap($e);
        }

        return [true];
    }

}
