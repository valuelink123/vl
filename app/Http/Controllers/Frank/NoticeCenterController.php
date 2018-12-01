<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use App\Models\KmsNotice;
use App\Models\KmsTag;
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

        $tags = KmsTag::getTagList('notice');

        return view('frank/kmsNotice', compact('itemGroupBrandModels', 'tags'));
    }

    /**
     * @throws \App\Traits\DataTablesException
     * @throws \App\Traits\MysqliException
     */
    public function get(Request $req) {

        $where = $this->dtWhere(
            $req,
            // f: 表示 fulltext，全文索引字段
            ['item_group', 'item_model', 'brand', 'title', 'f:content', 'f:tags'],
            ['item_group' => 'item_group', 'brand' => 'brand', 'item_model' => 'item_model']
        );

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

    /**
     * @throws \App\Traits\MysqliException
     */
    public function edit(Request $req) {

        $rows = $this->queryRows('SELECT item_group,brand,GROUP_CONCAT(DISTINCT item_model) AS item_models FROM asin GROUP BY item_group,brand');

        foreach ($rows as $row) {
            $itemGroupBrandModels[$row['item_group']][$row['brand']] = explode(',', $row['item_models']);
        }

        $tags = KmsTag::getTagList('notice');

        return view('frank.kmsNoticeCreate', compact('itemGroupBrandModels', 'tags'));
    }

    public function create(Request $req) {
        try {
            $row = KmsNotice::add($req->all());
            KmsTag::puts('notice', $req->input('tags'));
            return [$row->id, 'Data Written.'];
        } catch (\Exception $e) {
            return [false, $e->getMessage()];
        }
    }

}
