<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.28
 * Time: 10:22
 */

namespace App\Classes;

/**
 * Usage:
 *
 * $sap = new SapRfcRequest($appid, $appsecret, $host);
 *
 * $data = $sap->getOrder(['orderId' => '000-000-01']);
 *
 * $data = $sap->getAccessories(['sku' => 'TM0426']);
 *
 */

/**
 * Class SapRfcRequest
 * @package App\Classes
 * @method array getOrder(array $arguments)
 * @method array getAccessories(array $arguments)
 */
class SapRfcRequest {

    private $host;
    private $appid;
    private $appsecret;

    public function __construct($appid = 'site0001', $appsecret = 'testsite0001', $host = '116.6.105.153:18003') {
        $this->host = $host;
        $this->appid = $appid;
        $this->appsecret = $appsecret;
    }


    public static function sapOrderDataTranslate($data) {

        if (empty($data['O_ITEMS'])) return ['orderItems' => []];

        $order = array(
            'SellerId' => $data['SELLERID'],
            'MarketPlaceId' => $data['ZMPLACEID'],
            'AmazonOrderId' => $data['ZAOID'],
            'SellerOrderId' => $data['ZSOID'],
            'ApiDownloadDate' => date('Y-m-d H:i:s', strtotime($data['ALOADDATE'] . $data['ALOADTIME'])),
            'PurchaseDate' => date('Y-m-d H:i:s', strtotime($data['PCHASEDATE'] . $data['PCHASETIME'])),
            'LastUpdateDate' => date('Y-m-d H:i:s', strtotime($data['LUPDATEDATE'] . $data['LUPDATETIME'])),
            'OrderStatus' => $data['ORSTATUS'],
            'FulfillmentChannel' => $data['FCHANNEL'],
            'SalesChannel' => strtolower($data['SCHANNEL']),
            'OrderChannel' => $data['OCHANNEL'],
            'ShipServiceLevel' => $data['SHIPLEVEL'],
            'Name' => $data['ZNAME'],
            'AddressLine1' => $data['ADDR1'],
            'AddressLine2' => $data['ADDR2'],
            'AddressLine3' => $data['ADDR3'],
            'City' => $data['ZCITY'],
            'County' => $data['ZCOUNTRY'],
            'District' => $data['ZDISTRICT'],
            'StateOrRegion' => $data['ZSOREGION'],
            'PostalCode' => $data['ZPOSCODE'],
            'CountryCode' => $data['ZCOUNTRYCODE'],
            'Phone' => $data['ZPHONE'],
            'Amount' => $data['ZAMOUNT'],
            'CurrencyCode' => $data['ZCURRCODE'],
            'NumberOfItemsShipped' => $data['NISHIPPED'],
            'NumberOfItemsUnshipped' => $data['NIUNSHIPPED'],
            'PaymentMethod' => $data['PMETHOD'],
            'BuyerName' => $data['BUYNAME'],
            'BuyerEmail' => $data['BUYEMAIL'],
            'ShipServiceLevelCategory' => $data['SSCATEGORY'],
            'EarliestShipDate' => ($data['ESDATE'] > 0) ? date('Y-m-d H:i:s', strtotime($data['ESDATE'] . $data['ESTIME'])) : '',
            'LatestShipDate' => ($data['LSDATE'] > 0) ? date('Y-m-d H:i:s', strtotime($data['LSDATE'] . $data['LSTIME'])) : '',
            'EarliestDeliveryDate' => ($data['EDDATE'] > 0) ? date('Y-m-d H:i:s', strtotime($data['EDDATE'] . $data['EDTIME'])) : '',
            'LatestDeliveryDate' => ($data['LDDATE'] > 0) ? date('Y-m-d H:i:s', strtotime($data['LDDATE'] . $data['LDTIME'])) : '',
        );

        $MarketPlaceSite = 'www.' . $order['SalesChannel'];

        $orderItemData = [];

        foreach ($data['O_ITEMS'] as $sdata) {
            $orderItemData[] = array(
                'SellerId' => $sdata['SELLERID'],
                'MarketPlaceId' => $sdata['ZMPLACEID'],
                'AmazonOrderId' => $sdata['ZAOID'],
                'OrderItemId' => $sdata['ZORIID'],
                'Title' => $sdata['TITLE'],
                'QuantityOrdered' => intval($sdata['QORDERED']),
                'QuantityShipped' => intval($sdata['QSHIPPED']),
                'GiftWrapLevel' => $sdata['GWLEVEL'],
                'GiftMessageText' => $sdata['GMTEXT'],
                'ItemPriceAmount' => round($sdata['IPAMOUNT'], 2),
                'ItemPriceCurrencyCode' => $sdata['IPCCODE'],
                'ShippingPriceAmount' => round($sdata['SPAMOUNT'], 2),
                'ShippingPriceCurrencyCode' => $sdata['SPCCODE'],
                'GiftWrapPriceAmount' => round($sdata['GWPAMOUNT'], 2),
                'GiftWrapPriceCurrencyCode' => $sdata['GWPCCODE'],
                'ItemTaxAmount' => round($sdata['ITAMOUNT'], 2),
                'ItemTaxCurrencyCode' => $sdata['ITCCODE'],
                'ShippingTaxAmount' => round($sdata['STAMOUNT'], 2),
                'ShippingTaxCurrencyCode' => $sdata['STCCODE'],
                'GiftWrapTaxAmount' => round($sdata['GWTAMOUNT'], 2),
                'GiftWrapTaxCurrencyCode' => $sdata['GWTCCODE'],
                'ShippingDiscountAmount' => round($sdata['SDAMOUNT'], 2),
                'ShippingDiscountCurrencyCode' => $sdata['SDCCODE'],
                'PromotionDiscountAmount' => round($sdata['PDAMOUNT'], 2),
                'PromotionDiscountCurrencyCode' => $sdata['PDCCODE'],
                'PromotionIds' => $sdata['PROMOID'],
                'CODFeeAmount' => round($sdata['CFAMOUNT'], 2),
                'CODFeeCurrencyCode' => $sdata['CFCCODE'],
                'CODFeeDiscountAmount' => round($sdata['CFDAMOUNT'], 2),
                'CODFeeDiscountCurrencyCode' => $sdata['CFDCCODE'],
                'ASIN' => $sdata['ZASIN'],
                'SellerSKU' => $sdata['ZSSKU'],
                'MarketPlaceSite' => $MarketPlaceSite
            );
        }

        $order ['orderItems'] = $orderItemData;

        return $order;
    }

    /**
     * @throws SapRfcRequestException
     */
    public function __call($method, $arguments) {

        $appid = $this->appid;

        $pairs = $arguments[0];

        $array = compact('appid', 'method');
        $array = array_replace($array, $pairs);

        ksort($array);

        $authstr = [];

        foreach ($array as $k => $v) {
            $authstr[] = $k;
            $authstr[] = $v;
        }

        $authstr[] = $this->appsecret;

        $array['sign'] = strtoupper(sha1(implode('', $authstr)));

        $queryString = http_build_query($array, '', '&', PHP_QUERY_RFC3986);

        //$url = "http://{$this->host}/rfc_site.php?{$queryString}";
        $url = "http://192.168.10.10:18003/rfc_site.php?{$queryString}";

        try {

            $json = CurlRequest::curl_get_contents($url);

        } catch (\Exception $e) {

            throw new SapRfcRequestException('System Network Error.', 100, $e);
        }

        $json = json_decode($json, true);

        // 返回 json
        // 成功 {"method":"getOrder","orderId":"028-8067324-2327540","result":1,"message":""}
        // 失败 {"method":"getOrder","orderId":"028-8067324-23275401","result":0,"message":"Data Auth Failed!"}

        if (!isset($json['result'])) {
            throw new SapRfcRequestException('System Data Decode Failed.', 101);
        } else if (1 == $json['result']) {
            return $json['data'];
        } else if ('No matching data!' === $json['message']) {
            // 垃圾设计，把 无结果 和 错误 混在一起了！
            return [];
        } else {
            throw new SapRfcRequestException($json['message'], 102);
        }
    }
}

class SapRfcRequestException extends \Exception {

}
