<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use App\Classes\SapRfcRequest;
use App\Exceptions\DataInputException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartsListController extends Controller {

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    public function index() {
        return view('frank/kmsPartsList');
    }

    /**
     * 查询产品配件
     * @param $item_code
     * @return array
     */
    private function subItemCodes($item_code) {

        $subCodes = [];

        $sap = new SapRfcRequest();
        $rows = $sap->getAccessories(['sku' => $item_code]);
        foreach ($rows as $row) {
            $subCodes[] = $row['IDNRK'];
        }

        return $subCodes;
    }

    public function getSubItemList(Request $req) {

        try {
            $subCodes = $this->subItemCodes($req->input('item_code'));
        } catch (\Exception $e) {
            return [false, $e->getMessage()];
        }

        if (empty($subCodes)) return [];

        // kms_stock 为 fba_stockk + fbm_stock 的视图
        return DB::table('kms_stock')
            ->select('item_code', 'asin', 'fba_stock', 'fba_transfer', 'fbm_stock', 'item_name', 'seller_name', 'seller_sku')
            ->whereIn('item_code', $subCodes)
            ->get();

        // 改用视图
        // return DB::table('fba_stock AS t1')
        //     ->select(DB::raw('t1.item_code,t1.asin,t1.fba_stock,t1.fba_transfer,t2.fbm_stock,t2.item_name'))
        //     ->join('fbm_stock AS t2', 't1.item_code', '=', 't2.item_code')
        //     ->whereIn('t1.item_code', $subCodes)
        //     ->get();
        // ->toSql();

        // DB::enableQueryLog();
        // $user = User::get();
        // $query = DB::getQueryLog();
        // print_r($query);
    }

    /**
     * @throws \App\Traits\DataTablesException
     * @throws \App\Traits\MysqliException
     */
    public function get(Request $req) {

        $where = $this->dtWhere($req, ['item_code', 'item_name', 'asin', 'seller_id', 'seller_name', 'seller_sku'], ['item_group' => 't3.item_group', 'brand' => 't3.brand', 'item_model' => 't3.item_model']);

        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        // FROM fba_stock t1
        // INNER JOIN fbm_stock t2
        // USING(item_code)
        // 由于 INNER JOIN 导致数据不全，弃用

        $sql = "
        SELECT SQL_CALC_FOUND_ROWS
        t1.item_code,
        t1.seller_name,
        t1.asin,
        t1.seller_sku,
        t1.fba_stock,
        t1.fba_transfer,
        t1.fbm_stock,
        t1.item_name
        FROM kms_stock t1
        LEFT JOIN (
            SELECT
            ANY_VALUE(item_group) AS item_group,
            ANY_VALUE(brand) AS brand,
            ANY_VALUE(item_model) AS item_model,
            item_no AS item_code
            FROM asin
            GROUP BY item_no
        ) t3
        USING(item_code)
        WHERE $where
        ORDER BY $orderby
        LIMIT $limit
        ";

        $rows = $this->queryRows($sql);

        $total = $this->queryOne('SELECT FOUND_ROWS()');

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }

    /**
     * @throws DataInputException
     * @throws \App\Traits\MysqliException
     */
    public function getStockList(Request $req) {

        $item_code = $req->input('item_code');

        if (empty($item_code) || !preg_match('#^[A-z0-9]+$#', $item_code)) {
            throw new DataInputException("Wrong Item Code: {$item_code}");
        }

        $rows = $this->queryRows(
            "SELECT
                seller_name,
                seller_id,
                item_name,
                fba_stock AS stock
            FROM
                fba_stock
            LEFT JOIN fbm_stock USING (item_code)
            WHERE
                item_code = '{$item_code}'

            UNION

            SELECT
                'FBM' AS seller_name,
                'fbm' AS seller_id,
                item_name,
                fbm_stock AS stock
            FROM
                fbm_stock
            WHERE
                item_code = '{$item_code}'"
        );

        return $rows;
    }

}
