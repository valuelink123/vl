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
        return view('frank/kmsVideoList', ['abc' => 'Video List']);
    }

    public function get(Request $req) {

        $where = '1=1';

        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        $sql = "
SELECT SQL_CALC_FOUND_ROWS
v.item_group,v.item_model,v.type,v.descr,v.link,v.note,a.brand
FROM kms_video v
LEFT JOIN asin a ON a.item_group=v.item_group AND a.item_model=v.item_model AND a.brand is not null
WHERE $where
GROUP BY v.id
ORDER BY $orderby
LIMIT $limit
";

        $rows = $this->queryRows($sql, MYSQLI_ASSOC);

        $total = $this->queryOne('SELECT FOUND_ROWS()');

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }

}
