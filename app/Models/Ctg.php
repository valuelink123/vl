<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.21
 * Time: 16:31
 */

namespace App\Models;

use App\Asin;
use App\Classes\SapRfcRequest;
use App\Exceptions\HypocriteException;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Ctg extends Model {
    protected $primaryKey = null;
    public $incrementing = false; // 主键不是数字，需要有此声明，否则 order_id 被截断
    protected $table = 'ctg';
    // protected $guarded = ['id']; // 黑名单模式
    protected $fillable = ['order_id', 'gift_sku', 'name', 'email', 'phone', 'address', 'note', 'rating', 'processor'];

    /**
     * @throws HypocriteException
     */
    public static function add($row) {

        if (empty($row['order_id'])) throw new HypocriteException('ORDER ID IS UNSET');

        $row = array_map('trim', $row);

        try {

            $sap = new SapRfcRequest();

            $order = $sap->getOrder(['orderId' => $row['order_id']]);

            $order = SapRfcRequest::sapOrderDataTranslate($order);

            if (empty($order['orderItems'])) {
                throw new \Exception('Order Info Error.');
            }

            $item = current($order['orderItems']);

            $asinRow = Asin::select('sap_seller_id')->where('site', $item['MarketPlaceSite'])->where('asin', $item['ASIN'])->where('sellersku', $item['SellerSKU'])->first();

        } catch (\Exception $e) {
            throw new HypocriteException($e->getMessage() . ' For help, please mail to support@claimthegift.com');
        }


        try {

            DB::beginTransaction();

            // 时区不明确 todo
            $ctgRow = self::select('created_at')->where('created_at', '>', $order['PurchaseDate'])->where('order_id', $row['order_id'])->limit(1)->lockForUpdate()->first();

            if (!empty($ctgRow)) {
                DB::rollback();
                throw new \Exception("Order ID is duplicate, submitted in {$ctgRow['created_at']}.");
            }


            $row['processor'] = 0;

            if (!empty($asinRow->sap_seller_id)) {

                $user = User::select('id')->where('sap_seller_id', $asinRow->sap_seller_id)->limit(1)->first();

                if (!empty($user->id)) {
                    $row['processor'] = $user->id;
                }
            }


            $obj = self::create($row);

            // $find = [
            //     // 'created_at' => $order['created_at'],
            //     'MarketPlaceId' => $order['MarketPlaceId'],
            //     'SellerId' => $order['SellerId'],
            //     'AmazonOrderId' => $order['AmazonOrderId'],
            // ];
            // 去重效率成本太高，放弃；


            foreach ($order['orderItems'] as $item) {
                $item['created_at'] = $obj['created_at'];
                // CtgOrderItem::updateOrCreate($find, $item);
                CtgOrderItem::create($item);
            }

            unset($order['orderItems']);

            $order['created_at'] = $obj['created_at'];
            // CtgOrder::updateOrCreate($find, $order);
            CtgOrder::create($order);


            DB::commit();


            return $obj;

        } catch (\Exception $e) {
            throw new HypocriteException($e->getMessage() . ' For help, please mail to support@claimthegift.com');
            // return HypocriteException::wrap($e, 'ORDER ID is Duplicate, You may had submitted it successfully. For help, please mail to support@claimthegift.com');
        }
    }
}
