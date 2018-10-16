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

        $itemGroupModels = [];

        $rows = $this->queryRows('SELECT item_group, GROUP_CONCAT(DISTINCT item_model) AS item_models FROM kms_learn GROUP BY item_group');

        foreach ($rows as $row) {
            $itemGroupModels[$row['item_group']] = explode(',', $row['item_models']);
        }

        return view('frank/kmsLearn', compact('itemGroupModels'));
    }

    public function get(Request $req) {

        $where = $this->dtWhere($req, ['item_group', 'item_model', 'title', 'f:content'], ['item_group' => 'item_group', 'item_model' => 'item_model']);

        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS
title,
SUBSTRING(content, 1, 350) AS content
FROM kms_learn
WHERE $where
ORDER BY $orderby
LIMIT $limit
SQL;

        $vars['rows'] = $this->queryRows($sql);

        $vars['total'] = $this->queryOne('SELECT FOUND_ROWS()');

        return $vars;
    }

    public function edit(Request $req) {
        return view('frank.kmsLearnCreatee');
    }

    public function create(Request $req) {

    }

}
