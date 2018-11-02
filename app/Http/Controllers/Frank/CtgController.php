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

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    // 不需要登录验证
    protected static $authExcept = ['import'];

    /**
     * @throws \App\Traits\MysqliException
     * @throws \App\Traits\DataTablesException
     */
    public function list(Request $req) {

        if ($req->isMethod('GET')) {
            return view('frank.ctgList');
        }

        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        $sql = "
        SELECT
        DATE(created_at) AS created_at,
        name,
        email,
        phone,
        rating
        FROM ctg
        ORDER BY $orderby
        LIMIT $limit
        ";

        $data = $this->queryRows($sql);

        $recordsTotal = $recordsFiltered = $this->queryOne('SELECT FOUND_ROWS()');

        return compact('data', 'recordsTotal', 'recordsFiltered');

    }

    public function process(Request $req) {

        if ($req->isMethod('GET')) {
            return view('frank.ctgProcess');
        }
    }

    /**
     * 提交 CTG 数据
     * 由 claimthegift.com 调用
     * 加密方式及密码都是写好的
     *
     * @throws \App\Exceptions\HypocriteException
     */
    public function import(Request $req) {

        // todo remove
        if ($req->input('order_id')) {
            Ctg::add($req->all());
            return [true];
        }

        $binStr = $req->getContent();

        $json = openssl_decrypt($binStr, 'AES-256-CFB', 'frank-is-ok', OPENSSL_RAW_DATA, 'mnoefpaghijbcdkl');

        Ctg::add(json_decode($json, true));

        return [true];
    }

}
