<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use Illuminate\Http\Request;


class LearnCenterController extends Controller {

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    public function index(Request $req) {

        $where = $this->dtWhere($req, ['item_group', 'item_model', 'title', 'content'], []);

        // $orderby = $this->dtOrderBy($req);
        $orderby = 'updated_at DESC';
        $limit = $this->dtLimit($req);

        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS
title,
SUBSTRING(content, 1, 350) AS content
FROM kms_notice
WHERE $where
ORDER BY $orderby
LIMIT $limit
SQL;
        $vars['rows'] = $this->queryRows($sql);

        $vars['total'] = $this->queryOne('SELECT FOUND_ROWS()');

        return view('frank/kmsNotice', $vars);
    }

    public function get(Request $req) {

    }

}
