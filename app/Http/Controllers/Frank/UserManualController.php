<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use Illuminate\Http\Request;

class UserManualController extends Controller {

    use Traits\Mysqli;
    use Traits\DataTables;

    public function index() {
        return view('frank/kmsUserManual');
    }

    public function import() {
        $vars['itemGroups'] = $this->queryFields('SELECT item_group FROM asin GROUP BY item_group');
        $vars['itemGroupModels'] = $this->queryRows('SELECT item_group,GROUP_CONCAT(DISTINCT item_model) item_models FROM asin GROUP BY item_group');
        return view('frank/kmsUserManualCreate', $vars);
    }

    public function create(Request $req) {
        Models\KmsUserManual::create($req->all());
        // todo 提示成功
        return redirect('/kms/usermanual/import');
    }

    public function get(Request $req) {

        $where = $this->dtWhere($req, ['t2.sellersku', 't2.brand', 't2.asin', 't2.item_no', 't1.item_group', 't1.item_model'], ['item_group' => 't1.item_group', 'item_model' => 't1.item_model']);
        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);
        // todo item_name
        $sql = "
SELECT SQL_CALC_FOUND_ROWS
t1.item_group,t1.item_model,t1.link,t1.updated_at,t2.brand,t2.brand_line AS item_name
FROM kms_user_manual t1
LEFT JOIN asin t2 ON t2.item_group=t1.item_group AND t2.item_model=t1.item_model AND t2.brand is not null
WHERE $where
GROUP BY t1.id
ORDER BY $orderby
LIMIT $limit
";

        $rows = $this->queryRows($sql);

        $total = $this->queryOne('SELECT FOUND_ROWS()');

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }

}
