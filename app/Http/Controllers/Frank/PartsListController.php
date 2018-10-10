<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use App\Classes\SapRfcRequest;
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

        try {
            $sap = new SapRfcRequest();
            $rows = $sap->getAccessories(['sku' => $item_code]);
            foreach ($rows as $row) {
                $subCodes[] = $row['IDNRK'];
            }
        } catch (\Exception $e) {

        }

        return $subCodes;
    }

    public function getSubItemList(Request $req) {

        $subCodes = $this->subItemCodes($req->input('item_code'));

        if (empty($subCodes)) return [];

        return DB::table('fba_stock AS t1')
            ->select(DB::raw('t1.item_code,t1.asin,t1.fba_stock,t1.fba_transfer,t2.fbm_stock,t2.item_name'))
            ->join('fbm_stock AS t2', 't1.item_code', '=', 't2.item_code')
            ->whereIn('t1.item_code', $subCodes)
            ->get();
        // ->toSql();

        // DB::enableQueryLog();
        // $user = User::get();
        // $query = DB::getQueryLog();
        // print_r($query);
    }

    public function get(Request $req) {

        $where = $this->dtWhere($req, ['item_code', 'item_name', 'asin', 'seller_id', 'seller_name', 'seller_sku'], []);
        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        $sql = "
SELECT SQL_CALC_FOUND_ROWS
t1.item_code,
t1.seller_name,
t1.asin,
t1.seller_sku,
t1.fba_stock,
t1.fba_transfer,
t2.fbm_stock,
t2.item_name
FROM fba_stock t1
INNER JOIN fbm_stock t2
USING(item_code)
WHERE $where
ORDER BY $orderby
LIMIT $limit
";

        $rows = $this->queryRows($sql);

        $total = $this->queryOne('SELECT FOUND_ROWS()');

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }

}
