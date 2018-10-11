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

        $url = "http://{$this->host}/rfc_site.php?{$queryString}";

        try {

            $json = CurlRequest::curl_get_contents($url);

        } catch (\Exception $e) {

            throw new \Exception('System Network Error: ' . $e->getMessage());
        }

        $json = json_decode($json, true);

        // 返回 json
        // 成功 {"method":"getOrder","orderId":"028-8067324-2327540","result":1,"message":""}
        // 失败 {"method":"getOrder","orderId":"028-8067324-23275401","result":0,"message":"Data Auth Failed!"}

        if (!isset($json['result'])) {
            throw new \Exception('System Data Decode Failed.');
        } else if (1 == $json['result']) {
            return $json['data'];
        } else if ('No matching data!' === $json['message']) {
            // 垃圾设计，把 无结果 和 错误 混在一起了！
            return [];
        } else {
            throw new \Exception($json['message']);
        }
    }
}
