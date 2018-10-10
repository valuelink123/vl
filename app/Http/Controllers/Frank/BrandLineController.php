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

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    public function index() {
        // print_r(array_keys($GLOBALS));

        $itemGroupBrandModels = [];

        $rows = $this->queryRows('SELECT item_group,brand,GROUP_CONCAT(DISTINCT item_model) AS item_models FROM asin GROUP BY item_group,brand');

        foreach ($rows as $row) {
            $itemGroupBrandModels[$row['item_group']][$row['brand']] = explode(',', $row['item_models']);
        }

        return view('frank/kmsBrandLine', compact('itemGroupBrandModels'));
    }

    public function get(Request $req) {

        $where = $this->dtWhere($req, ['item_group', 'item_model', 'item_no', 'asin', 'sellersku', 'brand', 'brand_line'], ['item_group' => 'item_group', 'brand' => 'brand', 'item_model' => 'item_model']);

        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS
t1.brand,
t1.item_group,
t1.item_model,
ANY_VALUE(t1.brand_line) AS brand_line,
t2.manualink
FROM asin t1
LEFT JOIN (SELECT item_group,brand,item_model,any_value(link) manualink FROM kms_user_manual group by item_group,brand,item_model) t2
ON t2.item_group=t1.item_group AND t2.item_model=t1.item_model AND t2.brand=t1.brand
WHERE $where
GROUP BY item_group,brand,item_model
ORDER BY $orderby
LIMIT $limit
SQL;
        // $rows = DB::connection('frank')->table('asin')->select('item_group', 'item_model', 'brand')->groupBy('item_group', 'item_model')->get()->toArray();
        $rows = $this->queryRows($sql);

        $total = $this->queryOne('SELECT FOUND_ROWS()');

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }

}
