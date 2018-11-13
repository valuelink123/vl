<?php
/**
 * Created by PhpStorm.
 * Date: 18.10.30
 * Time: 10:05
 */

namespace App\Http\Controllers\Frank;


use App\Exceptions\DataInputException;
use App\Models\Ctg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            $rows = DB::table('users')->select('id', 'name')->get();

            foreach ($rows as $row) {
                $users[$row->id] = $row->name;
            }

            return view('frank.ctgList', compact('users'));
        }


        // query data list

        $where = $this->dtWhere($req, [], []);
        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        $sql = "
        SELECT SQL_CALC_FOUND_ROWS
            DATE(t1.created_at) AS created_at,
            t1.name,
            t1.email,
            phone,
            rating,
            commented,
            status,
            t2.name AS processor,
            order_id
        FROM ctg t1
        LEFT JOIN users t2
          ON t2.id = t1.processor
        WHERE $where
        ORDER BY $orderby
        LIMIT $limit
        ";

        $data = $this->queryRows($sql);

        $recordsTotal = $recordsFiltered = $this->queryOne('SELECT FOUND_ROWS()');

        return compact('data', 'recordsTotal', 'recordsFiltered');

    }

    /**
     * @throws DataInputException
     */
    public function process(Request $req) {

        $ctgRow = Ctg::selectRaw('*')->where('order_id', $req->input('order_id'))->first();

        if (empty($ctgRow)) throw new DataInputException('ctg not found');

        if ($req->isMethod('GET')) {

            $rows = DB::table('users')->select('id', 'name')->get();

            foreach ($rows as $row) {
                $users[$row->id] = $row->name;
            }

            return view('frank.ctgProcess', compact('ctgRow', 'users'));

        }


        // Update

        if ($req->has('processor')) {
            $ctgRow->processor = (int)$req->input('processor');
        }

        if ($req->has('steps')) {
            $ctgRow->status = $req->input('status');
            $ctgRow->commented = $req->input('commented');
            $ctgRow->steps = json_encode($req->input('steps'));
        }

        $ctgRow->save();

        return [true];
    }

    /**
     * 提交 CTG 数据
     * 由 claimthegift.com 调用
     * 加密方式及密码都是写好的
     *
     * @throws \App\Exceptions\HypocriteException
     */
    public function import(Request $req) {

        $binStr = $req->getContent();

        $json = openssl_decrypt($binStr, 'AES-256-CFB', 'frank-is-ok', OPENSSL_RAW_DATA, 'mnoefpaghijbcdkl');

        Ctg::add(json_decode($json, true));

        return [true];
    }

}
