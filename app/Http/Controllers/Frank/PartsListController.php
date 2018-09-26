<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use Illuminate\Http\Request;

class PartsListController extends Controller {

    use Traits\Mysqli;
    use Traits\DataTables;

    public function index() {
        return view('frank/kmsPartsList');
    }

    public function get(Request $req) {

        $where = $this->dtWhere($req, ['sellersku', 'brand', 'brand_line', 'asin', 'item_no', 'item_group', 'item_model'], ['item_group' => 'item_group', 'brand' => 'brand', 'item_model' => 'item_model']);
        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);
// todo data from sap
        $sql = "
SELECT SQL_CALC_FOUND_ROWS
item_no,MAX(brand) AS item_name,ANY_VALUE(group_id) AS stock,ANY_VALUE(review_user_id) AS apd
FROM asin
WHERE $where
GROUP BY item_no
ORDER BY $orderby
LIMIT $limit
";

        $rows = $this->queryRows($sql);

        $total = $this->queryOne('SELECT FOUND_ROWS()');

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }

}
