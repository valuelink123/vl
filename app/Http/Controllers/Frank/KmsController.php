<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;


class KmsController extends Controller {

    public function brandLine() {
        // print_r(($GLOBALS['request'])); // 竟然报内存不足
        // print_r(request()->route()->getAction());
        return view('frank/kmsBrandLine', ['abc' => 'Product Guide']);
    }

}
