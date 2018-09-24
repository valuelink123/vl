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
        // print_r(array_keys($GLOBALS));
        return view('frank/kmsBrandLine');
    }

    public function get(Request $req) {

        $where = $this->dtWhere($req, ['item_group', 'item_model', 'item_no', 'asin', 'sellersku', 'brand', 'brand_line'], []);

        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        // $rows = DB::connection('frank')->table('asin')->select('item_group', 'item_model', 'brand')->groupBy('item_group', 'item_model')->get()->toArray();
        $rows = $this->queryRows("SELECT SQL_CALC_FOUND_ROWS item_group,brand,item_model FROM asin WHERE $where GROUP BY item_group,item_model ORDER BY $orderby LIMIT $limit");
        $total = $this->queryOne('SELECT FOUND_ROWS()');

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }

}
