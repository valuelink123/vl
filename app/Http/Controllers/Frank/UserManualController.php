<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use Illuminate\Http\Request;
use App\Models\KmsUserManual;

class UserManualController extends Controller {

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    public function index() {
        return view('frank/kmsUserManual');
    }

    /**
     * @throws \App\Traits\MysqliException
     */
    public function import() {

        $rows = $this->queryRows('SELECT item_group,brand,GROUP_CONCAT(DISTINCT item_model) AS item_models FROM asin GROUP BY item_group,brand');

        foreach ($rows as $row) {
            $vars['itemGroupBrandModels'][$row['item_group']][$row['brand']] = explode(',', $row['item_models']);
        }

        return view('frank/kmsUserManualCreate', $vars);
    }

    /**
     * @throws \App\Exceptions\DataImportException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function create(Request $req) {

        KmsUserManual::import($req);
        // qa 加 type dot 连个字段，可选择、可编辑
        // 下载过渡页
        // Parts List 物料主从关系
        // 主题，菜单看不清楚
        // 可编辑 table
        // search 加强 多关键字 and
        // 根据 link 去重
        // 仅显示有匹配的 view
        // todo 提示成功
        return redirect('/kms/usermanual/import');
    }

    /**
     * @throws \App\Traits\MysqliException
     * @throws \App\Traits\DataTablesException
     */
    public function get(Request $req) {

        $where = $this->dtWhere($req, ['t2.sellersku', 't1.brand', 't2.brand_line', 't2.asin', 't2.item_no', 't1.item_group', 't1.item_model'], ['item_group' => 't1.item_group', 'brand' => 't1.brand', 'item_model' => 't1.item_model']);
        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);
        // todo UPDATE asin SET xxx=TRIM(IFNULL(xxx, ''))
        $sql = "
SELECT SQL_CALC_FOUND_ROWS
item_group,
item_model,
link,
updated_at,
brand,
t2.brand_line
FROM kms_user_manual t1
LEFT JOIN (
  SELECT item_group,brand,item_model,
  GROUP_CONCAT(DISTINCT sellersku) AS sellersku,
  GROUP_CONCAT(DISTINCT brand_line) AS brand_line,
  GROUP_CONCAT(DISTINCT asin) AS asin,
  GROUP_CONCAT(DISTINCT item_no) AS item_no
  FROM asin
  GROUP BY item_group,brand,item_model
) t2
USING(item_group,brand,item_model)
WHERE $where
ORDER BY $orderby
LIMIT $limit
";

        $rows = $this->queryRows($sql);

        $total = $this->queryOne('SELECT FOUND_ROWS()');

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }

}
