<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use Illuminate\Http\Request;

class VideoListController extends Controller {

    use Traits\Mysqli;
    use Traits\DataTables;

    public function index() {
        return view('frank/kmsVideoList');
    }

    public function create() {
        return view('frank/kmsVideoCreate');
    }

    public function get(Request $req) {

        // 搜索分两种，search 参数格式，多个字段怎么搞
        // MySQL明确字段查询
        // 全文索引
        if (!empty($req->input('search.item_group'))) {
            $item_group = addslashes($req->input('search.item_group'));
            $item_model = addslashes($req->input('search.item_model'));
            $where = "t1.item_group='{$item_group}' AND t1.item_model='{$item_model}'";
        } else if (!empty($req->input('search.value'))) {
            $word = addslashes($req->input('search.value'));
            $fields = ['t2.sellersku', 't2.brand', 't2.asin', 't2.item_no', 't1.item_group', 't1.item_model'];
            $where = [];
            foreach ($fields as $field) {
                $where[] = "{$field} LIKE '%{$word}%'";
            }
            $where = implode(' OR ', $where);
        } else {
            $where = 1;
        }
        // $where = $this->dtWhere($req);
        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        // 非唯一索引关联的情况下，Left Join 是会出现重复数据的；这里使用 Group By 左表 id 来去重；
        $sql = "
SELECT SQL_CALC_FOUND_ROWS
t1.item_group,t1.item_model,t1.type,t1.descr,t1.link,t1.note,t2.brand
FROM kms_video t1
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
