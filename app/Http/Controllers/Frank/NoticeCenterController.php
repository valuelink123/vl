<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use Illuminate\Http\Request;


class NoticeCenterController extends Controller {

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    /**
     * @throws \App\Traits\MysqliException
     */
    public function index(Request $req) {

        $rows = $this->queryRows('SELECT item_group,brand,GROUP_CONCAT(DISTINCT item_model) AS item_models FROM kms_notice GROUP BY item_group,brand');

        foreach ($rows as $row) {
            $itemGroupBrandModels[$row['item_group']][$row['brand']] = explode(',', $row['item_models']);
        }

        return view('frank/kmsNotice', compact('itemGroupBrandModels'));
    }

    /**
     * @throws \App\Traits\DataTablesException
     * @throws \App\Traits\MysqliException
     */
    public function get(Request $req) {

        $where = $this->dtWhere($req, ['item_group', 'item_model', 'brand', 'title', 'f:content'], ['item_group' => 'item_group', 'brand' => 'brand', 'item_model' => 'item_model']);

        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        $sql = "
        SELECT SQL_CALC_FOUND_ROWS
        title,
        SUBSTRING(content, 1, 350) AS content
        FROM kms_notice
        WHERE $where
        ORDER BY $orderby
        LIMIT $limit
        ";

        // throw new \Exception($sql);

        $vars['rows'] = $this->queryRows($sql);

        $vars['total'] = $this->queryOne('SELECT FOUND_ROWS()');

        return $vars;
    }

    public function edit(Request $req) {

        $rows = $this->queryRows('SELECT item_group,brand,GROUP_CONCAT(DISTINCT item_model) AS item_models FROM asin GROUP BY item_group,brand');

        foreach ($rows as $row) {
            $itemGroupBrandModels[$row['item_group']][$row['brand']] = explode(',', $row['item_models']);
        }

        return view('frank.kmsNoticeCreate', compact('itemGroupBrandModels'));
    }

    public function create(Request $req) {

    }

}
