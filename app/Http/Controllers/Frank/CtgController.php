<?php
/**
 * Created by PhpStorm.
 * Date: 18.10.30
 * Time: 10:05
 */

namespace App\Http\Controllers\Frank;


use App\Models\Ctg;
use Illuminate\Http\Request;

class CtgController extends Controller {

    // 不需要登录验证
    protected static $authExcept = ['import'];

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
     * 提交 CTG 数据，由 claimthegift.com 调用
     * @throws \App\Exceptions\HypocriteException
     */
    public function import(Request $req) {

        Ctg::add($req->all());

        return [true];
    }

}
