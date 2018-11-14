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
use Illuminate\Database\Eloquent\Model;

class Ctg extends Model {
    protected $primaryKey = 'order_id';
    protected $table = 'ctg';
    // protected $guarded = ['id']; // 黑名单模式
    protected $fillable = ['order_id', 'gift_sku', 'name', 'email', 'phone', 'address', 'note', 'rating'];

    /**
     * @throws HypocriteException
     */
    public static function add($row) {

        if (empty($row['order_id'])) throw new HypocriteException('ORDER ID IS UNSET');

        $row = array_map('trim', $row);

        try {

            $sap = new SapRfcRequest();

            $order = $sap->getOrder(['orderId' => $row['order_id']]);

            if (empty($order['O_ITEMS'])) {
                throw new \Exception('Order Info Error.');
            }

            $item = array_pop($order['O_ITEMS']);

            $site = strtolower("www.{$order['SCHANNEL']}");

            $asinRow = Asin::select('sap_seller_id')->where('site', $site)->where('asin', $item['ZASIN'])->where('sellersku', $item['ZSSKU'])->first();

        } catch (\Exception $e) {
            throw new HypocriteException($e->getMessage() . ' For help, please mail to support@claimthegift.com');
        }


        try {

            $obj = self::create($row);

            if (!empty($asinRow->sap_seller_id)) {

                $user = User::select('id')->where('sap_seller_id', $asinRow->sap_seller_id)->limit(1)->first();

                if (!empty($user->id)) {
                    $obj->processor = $user->id;
                    $obj->save();
                }
            }

            return $obj;

        } catch (\Exception $e) {
            throw new HypocriteException('ORDER ID is Duplicate, You may had submitted it successfully. For help, please mail to support@claimthegift.com');
            // return HypocriteException::wrap($e, 'ORDER ID is Duplicate, You may had submitted it successfully. For help, please mail to support@claimthegift.com');
        }
    }
}
