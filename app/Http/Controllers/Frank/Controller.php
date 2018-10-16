<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.15
 * Time: 15:16
 */

namespace App\Http\Controllers\Frank;


class Controller extends \App\Http\Controllers\Controller {

    public function __construct() {
        app('debugbar')->disable();
        $this->middleware('auth');
    }

}
