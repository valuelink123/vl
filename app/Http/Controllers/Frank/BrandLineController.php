<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use Illuminate\Http\Request;

// use Illuminate\Support\Facades\DB;

class BrandLineController extends Controller {

    use Traits\Mysqli;
    use Traits\DataTables;

    public function index() {
        // print_r(($GLOBALS['request'])); // 竟然报内存不足
        // print_r(request()->route()->getAction());
        return view('frank/kmsBrandLine');
    }

    public function get(Request $req) {

        // 提供按 item_no asin brand_linre 搜索

        $where = '1=1';

        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        // $rows = DB::connection('frank')->table('asin')->select('item_group', 'item_model', 'brand')->groupBy('item_group', 'item_model')->get()->toArray();
        $rows = $this->queryRows("SELECT SQL_CALC_FOUND_ROWS item_group,brand,item_model FROM asin WHERE $where GROUP BY item_group,item_model ORDER BY $orderby LIMIT $limit");
        $total = $this->queryOne('SELECT FOUND_ROWS()');

        // foreach ($rows as &$row) {}

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }

}
