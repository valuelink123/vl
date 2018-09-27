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
        $vars['brands'] = $this->queryFields('SELECT brand FROM asin GROUP BY brand');
        $vars['itemGroupModels'] = $this->queryRows('SELECT item_group,GROUP_CONCAT(DISTINCT item_model) item_models FROM asin GROUP BY item_group');
        return view('frank/kmsUserManualCreate', $vars);
    }

    public function create(Request $req) {

        Models\KmsUserManual::import($req);
        // qa 加 type dot 连个字段，可选择、可编辑
        // 下载过渡页
        // Parts List 物料主从关系
        // 主题，菜单看不清楚
        // 可编辑 table
        // search 加强 多关键字 or and
        // 根据 link 去重
        // 仅显示有匹配的 view
        // todo 提示成功
        return redirect('/kms/usermanual/import');
    }

    public function get(Request $req) {

        $where = $this->dtWhere($req, ['t2.sellersku', 't1.brand', 't2.asin', 't2.item_no', 't1.item_group', 't1.item_model'], ['item_group' => 't1.item_group', 'brand' => 't1.brand', 'item_model' => 't1.item_model']);
        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);
        // todo item_name
        $sql = "
SELECT SQL_CALC_FOUND_ROWS
ANY_VALUE(t1.item_group) AS item_group,
ANY_VALUE(t1.item_model) AS item_model,
ANY_VALUE(t1.link) AS link,
ANY_VALUE(t1.updated_at) AS updated_at,
ANY_VALUE(t1.brand) AS brand,
MAX(t2.brand_line) AS item_group_descr
FROM kms_user_manual t1
LEFT JOIN asin t2 ON t2.item_group=t1.item_group AND t2.brand=t1.brand AND t2.item_model=t1.item_model
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
