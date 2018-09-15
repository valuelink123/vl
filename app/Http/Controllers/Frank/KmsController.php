<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;


class KmsController extends Controller {

    public function test() {
        // print_r(request()->route()->getAction());
        return view('kms/test', ['abc' => 'Product Guide']);
    }

}
