<?php
/**
 * Created by PhpStorm.
 * Date: 18.10.30
 * Time: 10:05
 */

namespace App\Http\Controllers\Frank;


use App\Accounts;
use App\Classes\SapRfcRequest;
use App\Exceptions\DataInputException;
use App\Models\Ctg;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CtgController extends Controller {

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    // 不需要登录验证
    protected static $authExcept = ['import'];

    /**
     * @throws \App\Traits\MysqliException
     * @throws \App\Traits\DataTablesException
     */
    public function list(Request $req) {

        if ($req->isMethod('GET')) {

            $userRows = DB::table('users')->select('id', 'name')->get();

            foreach ($userRows as $row) {
                $users[$row->id] = $row->name;
            }

            $bgs = $this->queryFields('SELECT DISTINCT bg FROM asin');
            $bus = $this->queryFields('SELECT DISTINCT bu FROM asin');
            $brands = $this->queryFields('SELECT DISTINCT brand FROM asin');

            return view('frank.ctgList', compact('users', 'bgs', 'bus', 'brands'));
        }


        // query data list

        $dateRange = $this->dtDateRange($req);
        $where = $this->dtWhere(
            $req,
            [
                'processor' => 't2.name',
                'email' => 't1.email',
                'name' => 't1.name',
                'order_id' => 't1.order_id',
                'asins' => 't4.asins',
                'itemCodes' => 't4.itemCodes',
                'itemNames' => 't4.itemNames',
                'sellerskus' => 't4.sellerskus',
                'itemGroups' => 't4.itemGroups',
                'brands' => 't4.brands',
                'bgs' => 't4.bgs',
                'bus' => 't4.bus',
                'phone' => 't1.phone'
            ],
            [
                'phone' => 't1.phone'
            ],
            [
                'rating' => 't1.rating',
                'processor' => 't1.processor',
                'status' => 't1.status',
                // todo 已知的搜索问题
                'bg' => 't4.bgs',
                'bu' => 't4.bus',
                'brand' => 't4.brands',
            ]
        );
        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        $sql = "
        SELECT SQL_CALC_FOUND_ROWS
            t1.created_at,
            t1.name,
            t1.email,
            t1.phone,
            rating,
            commented,
            steps,
            status,
            t2.name AS processor,
            t1.order_id,
            t4.asins,
            t4.itemCodes,
            t4.itemNames,
            t4.sellerskus,
            t4.itemGroups,
            t4.bgs,
            t4.bus,
            t4.brands
        FROM ctg t1
        LEFT JOIN users t2
          ON t2.id = t1.processor
        LEFT JOIN (
          SELECT
            MarketPlaceId,
            SellerId,
            AmazonOrderId
          FROM ctg_order
            WHERE $dateRange
          ) t3
          ON t3.AmazonOrderId = t1.order_id
        LEFT JOIN (
            SELECT
              ANY_VALUE(SellerId) AS SellerId,
              ANY_VALUE(MarketPlaceId) AS MarketPlaceId,
              ANY_VALUE(AmazonOrderId) AS AmazonOrderId,
              GROUP_CONCAT(DISTINCT t4_1.ASIN) AS asins,
              GROUP_CONCAT(DISTINCT asin.item_no) AS itemCodes,
              GROUP_CONCAT(DISTINCT fbm_stock.item_name) AS itemNames,
              GROUP_CONCAT(DISTINCT asin.sellersku) AS sellerskus,
              GROUP_CONCAT(DISTINCT asin.item_group) AS itemGroups,
              GROUP_CONCAT(DISTINCT asin.bg) AS bgs,
              GROUP_CONCAT(DISTINCT asin.bu) AS bus,
              GROUP_CONCAT(DISTINCT asin.brand) AS brands
            FROM ctg_order_item t4_1
            LEFT JOIN asin
              ON asin.site = t4_1.MarketPlaceSite AND asin.asin = t4_1.ASIN AND asin.sellersku = t4_1.SellerSKU
            LEFT JOIN fbm_stock
              ON fbm_stock.item_code = asin.item_no
            WHERE $dateRange
            GROUP BY AmazonOrderId
          ) t4
          ON t4.AmazonOrderId = t1.order_id AND t4.MarketPlaceId = t3.MarketPlaceId AND t4.SellerId = t3.SellerId
        WHERE $where
        ORDER BY $orderby
        LIMIT $limit
        ";

        $data = $this->queryRows($sql);

        $recordsTotal = $recordsFiltered = $this->queryOne('SELECT FOUND_ROWS()');

        return compact('data', 'recordsTotal', 'recordsFiltered');

    }

    public function batchAssignTask(Request $req) {

        if (empty($req->input('ctgRows'))) return [true, ''];

        $processor = (int)$req->input('processor');

        $user = User::findOrFail($processor);

        Ctg::where(function ($where) use ($req) {
            foreach ($req->input('ctgRows') as $row) {
                // WHERE GROUP，传二维数组就可以
                $where->orWhere([
                    ['created_at', $row[0]],
                    ['order_id', $row[1]],
                ]);
            }
        })->update(compact('processor'));

        // foreach ($req->input('order_ids') as $order_id) {
        //     // 干掉 id 字段，使用表分区
        //     // 要求 select 中包含主键，否则无法保存
        //     $row = Ctg::select('id')->where('order_id', $order_id)->first();
        //     $row->processor = $processor;
        //     $row->save();
        // }

        return [true, $user->name];
    }

    /**
     * @throws DataInputException
     */
    public function process(Request $req) {

        $wheres = [
            ['created_at', $req->input('created_at')],
            ['order_id', $req->input('order_id')]
        ];

        $ctgRow = Ctg::selectRaw('*')->where($wheres)->limit(1)->first();

        if (empty($ctgRow)) throw new DataInputException('ctg not found');

        if ($req->isMethod('GET')) {

            $sap = new SapRfcRequest();

            $order = SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $req->input('order_id')]));

            $order['SellerName'] = Accounts::where('account_sellerid', $order['SellerId'])->first()->account_name ?? 'No Match';


            $emails = DB::table('sendbox')->where('to_address', $order['BuyerEmail'])->orderBy('date', 'desc')->get();
            $emails = json_decode(json_encode($emails), true); // todo


            $userRows = DB::table('users')->select('id', 'name')->get();

            foreach ($userRows as $row) {
                $users[$row->id] = $row->name;
            }


            return view('frank.ctgProcess', compact('ctgRow', 'users', 'order', 'emails'));

        }


        // Update

        $updates = [];

        if ($req->has('processor')) {
            $updates['processor'] = (int)$req->input('processor');
        }

        if ($req->has('steps')) {
            $updates['status'] = $req->input('status');
            $updates['commented'] = $req->input('commented');
            $updates['steps'] = json_encode($req->input('steps'));
        }

        $ctgRow->where($wheres)->update($updates);

        return [true];
    }

    /**
     * 提交 CTG 数据
     * 由 claimthegift.com 调用
     * 加密方式及密码都是写好的
     *
     * @throws \App\Exceptions\HypocriteException
     */
    public function import(Request $req) {

        $binStr = $req->getContent();

        $json = openssl_decrypt($binStr, 'AES-256-CFB', 'frank-is-ok', OPENSSL_RAW_DATA, 'mnoefpaghijbcdkl');

        Ctg::add(json_decode($json, true));

        return [true];
    }

}
