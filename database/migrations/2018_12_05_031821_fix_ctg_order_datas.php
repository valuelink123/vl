<?php

use App\Asin;
use App\Classes\SapRfcRequest;
use App\Models\Ctg;
use App\Models\CtgOrder;
use App\Models\CtgOrderItem;
use App\User;
use Illuminate\Database\Migrations\Migration;

class FixCtgOrderDatas extends Migration {
    use \App\Traits\Mysqli;
    use \App\Traits\Migration;

    /**
     * @throws \App\Traits\MysqliException
     */
    public function up() {

        $offset = 0;

        do {

            $ctgRows = $this->queryRows(
                "
                SELECT
                  processor, order_id, ctg.created_at
                FROM ctg
                LEFT JOIN ctg_order
                ON ctg.created_at = ctg_order.created_at AND ctg.order_id = ctg_order.AmazonOrderId
                WHERE ctg_order.AmazonOrderId IS NULL
                LIMIT 100
                OFFSET $offset
                "
            );

            foreach ($ctgRows as $row) {

                $sap = new SapRfcRequest();

                $order = $sap->getOrder(['orderId' => $row['order_id']]);
                $order = SapRfcRequest::sapOrderDataTranslate($order);
                $order['created_at'] = $row['created_at'];


                if ($row['processor'] <= 0) {

                    $item = current($order['orderItems']);
                    $asinRow = Asin::select('sap_seller_id')->where('site', $item['MarketPlaceSite'])->where('asin', $item['ASIN'])->where('sellersku', $item['SellerSKU'])->first();

                    if (!empty($asinRow->sap_seller_id)) {

                        $user = User::select('id')->where('sap_seller_id', $asinRow->sap_seller_id)->limit(1)->first();

                        if (!empty($user->id)) {
                            Ctg::where([
                                ['created_at', $row['created_at']],
                                ['order_id', $row['order_id']]
                            ])->update([
                                'processor' => $user->id
                            ])->limit(1);
                        }
                    }

                }

                foreach ($order['orderItems'] as $item) {
                    $item['created_at'] = $row['created_at'];
                    CtgOrderItem::create($item);
                }

                unset($order['orderItems']);

                CtgOrder::create($order);
            }

            $offset += 100;

        } while (!empty($ctgRows));
    }

    public function down() {
        //
    }

    public function __destruct() {
    }
}
