<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use Illuminate\Http\Request;
use App\Models\KmsVideo;

class VideoListController extends Controller {

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    public function index() {
        return view('frank/kmsVideoList');
    }

    public function import() {

        // 一对多关系，很实用的 SQL 语句
        $rows = $this->queryRows('SELECT item_group,brand,GROUP_CONCAT(DISTINCT item_model) AS item_models FROM asin GROUP BY item_group,brand');

        foreach ($rows as $row) {
            $vars['itemGroupBrandModels'][$row['item_group']][$row['brand']] = explode(',', $row['item_models']);
        }

        $vars['types'] = $this->enumOptions('kms_video', 'type');

        return view('frank/kmsVideoCreate', $vars);
    }

    public function create(Request $req) {

        try {
            $videoTypes = $this->enumOptions('kms_video', 'type');
            $count = KmsVideo::import($req, $videoTypes);
            $errors = ['success' => "Written $count Records."];
        } catch (\Exception $e) {
            $errors = ['error' => $e->getMessage()];
        }

        return redirect()->back()->withErrors($errors, 'dataImport');
    }

    public function get(Request $req) {

        $where = $this->dtWhere($req, ['t2.sellersku', 't1.brand', 't2.brand_line', 't2.asin', 't2.item_no', 't1.item_group', 't1.item_model', 't1.descr', 't1.link', 't1.note'], ['item_group' => 't1.item_group', 'brand' => 't1.brand', 'item_model' => 't1.item_model']);
        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        // 非唯一索引关联的情况下，Left Join 是会出现重复数据的；这里使用 Group By 左表 id 来去重；
        $sql = "
        SELECT SQL_CALC_FOUND_ROWS
        item_group,
        brand,
        item_model,
        type,
        descr,
        link,
        note,
        t2.brand_line
        FROM kms_video t1
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
