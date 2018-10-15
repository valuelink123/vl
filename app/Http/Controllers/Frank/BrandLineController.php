<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use App\Asin;
use App\Models\KmsUserManual;
use App\Models\KmsVideo;
use Illuminate\Http\Request;

// use Illuminate\Support\Facades\DB;

class BrandLineController extends Controller {

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    public function index() {
        // print_r(array_keys($GLOBALS));

        $rows = $this->queryRows('SELECT item_group,brand,GROUP_CONCAT(DISTINCT item_model) AS item_models FROM asin GROUP BY item_group,brand');

        foreach ($rows as $row) {
            $itemGroupBrandModels[$row['item_group']][$row['brand']] = explode(',', $row['item_models']);
        }

        return view('frank/kmsBrandLine', compact('itemGroupBrandModels'));
    }

    public function get(Request $req) {

        $where = $this->dtWhere($req, ['item_group', 'item_model', 'item_no', 'asin', 'sellersku', 'brand', 'brand_line'], ['item_group' => 'item_group', 'brand' => 'brand', 'item_model' => 'item_model']);

        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS
t1.brand,
t1.item_group,
t1.item_model,
ANY_VALUE(t1.brand_line) AS brand_line,
t2.manualink,
IF(ISNULL(t3.item_group), 0, 1) AS has_video,
COUNT(t4.item_code) AS has_stock_info
FROM asin t1
LEFT JOIN (SELECT item_group,brand,item_model,any_value(link) manualink FROM kms_user_manual GROUP BY item_group,brand,item_model) t2
USING(item_group,brand,item_model)
LEFT JOIN (SELECT DISTINCT item_group,brand,item_model FROM kms_video) t3
USING(item_group,brand,item_model)
LEFT JOIN kms_stock t4
ON t4.item_code=t1.item_no
WHERE $where
GROUP BY item_group,brand,item_model
ORDER BY $orderby
LIMIT $limit
SQL;
        // $rows = DB::connection('frank')->table('asin')->select('item_group', 'item_model', 'brand')->groupBy('item_group', 'item_model')->get()->toArray();
        $rows = $this->queryRows($sql);

        $total = $this->queryOne('SELECT FOUND_ROWS()');

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }

    /**
     * @throws \App\Traits\MysqliException
     */
    public function getEmailDetailRightBar(Request $req) {

        $asinRows = $req->input('asinRows', []);

        if (empty($asinRows)) return [];

        // $where = [];
        //
        // foreach ($asinRows as $row) {
        //
        //     // if (!preg_match('#^[\w.]+$#', $row['site'])) throw new DataInputException("Site - {$row['site']} format error.", 100);
        //     // if (!preg_match('#^\w+$#', $row['asin'])) throw new DataInputException("Asin - {$row['asin']} format error.", 100);
        //     // if (!preg_match('#^[\w-]+$#', $row['sellersku'])) throw new DataInputException("SellerSKU - {$row['sellersku']} format error.", 100);
        //
        //     foreach ($row as &$field) $field = addslashes($field);
        //
        //     $where[] = "(site='{$row['site']}' AND asin='{$row['asin']}' AND sellersku='{$row['sellersku']}')";
        // }
        //
        // $where = implode(' OR ', $where);
        //
        // $xxx = Asin::select('item_group', 'brand', 'item_model')->whereRaw($where)->orWhereRaw($where)->toSql();

        $modelRows = Asin::select('item_group', 'brand', 'item_model')->where(function ($where) use ($asinRows) {
            foreach ($asinRows as $row) {
                $where->orWhere(function ($where) use ($row) {
                    $where->where('site', $row['site']);
                    $where->where('asin', $row['asin']);
                    $where->where('sellersku', $row['sellersku']);
                });
            }
        })->get();

        if (empty($modelRows)) return [];

        // 能不用 JOIN 就尽量不用
        // 有唯一索引，不用那么绕
        // 不过 Eloquent 也真够绕的
        $manuals = KmsUserManual::select('link')->where(function ($where) use ($modelRows) {
            foreach ($modelRows as $row) {
                $where->orWhere(function ($where) use ($row) {
                    $where->where('brand', $row->brand);
                    $where->where('item_group', $row->item_group);
                    $where->where('item_model', $row->item_model);
                });
            }
        })->get();

        $videos = KmsVideo::select('link')->where(function ($where) use ($modelRows) {
            foreach ($modelRows as $row) {
                $where->orWhere(function ($where) use ($row) {
                    $where->where('brand', $row->brand);
                    $where->where('item_group', $row->item_group);
                    $where->where('item_model', $row->item_model);
                });
            }
        })->get();

        return compact('manuals', 'videos');
    }

}
