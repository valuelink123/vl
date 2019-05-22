<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.15
 * Time: 15:16
 */

namespace App\Http\Controllers\Frank;

use Illuminate\Support\Facades\Auth;
class Controller extends \App\Http\Controllers\Controller {

    protected static $authExcept = [];

    public function __construct() {
        app('debugbar')->disable();
        $this->middleware('auth', ['except' => static::$authExcept]);
    }

}
