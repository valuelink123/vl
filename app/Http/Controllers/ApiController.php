<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Classes\CurlRequest;
use DB;

class ApiController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	//通过订单号从sap平台获取订单数据
	public function getOrderDataBySap()
	{
		$orderId = $_REQUEST['orderid'];
		$appid = 'site0001';
		$appsecret = 'testsite0001';
		$method = 'getOrder';

		$array = compact('orderId', 'appid', 'method');
		ksort($array);
		$authstr = [];
		foreach ($array as $k => $v) {
			$authstr[] = $k;
			$authstr[] = $v;
		}
		$authstr[] = $appsecret;

		$sign = strtoupper(sha1(implode('', $authstr)));
		$host = '113.108.40.136:18003';

		$url = "http://{$host}/rfc_site.php?appid={$appid}&method={$method}&orderId={$orderId}&sign={$sign}";
		// $url = "http://192.168.10.10:18003/rfc_site.php?appid=site0001&method=getOrder&orderId=113-5687773-9463408&sign=A04B065C3ABFB819E4200A8CBF4729C538EDA5DB";
		try {
			$json = CurlRequest::curl_get_contents($url);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}

		$json = json_decode($json, true);//转化成数组
		$orderData = $json['data'];//order数据与order_items数据
		$orderData['O_ITEMS'][0] = $orderData['O_ITEMS'][1];//将O_ITEMS数据下的键为1转化为0,因为ctg官网用的key是0
		unset($orderData['O_ITEMS'][1]);
		if (!isset($json['result'])) {
			throw new \Exception('System data decode failed.');
		} else if (1 == $json['result']) {
			return $orderData;
		} else {
			throw new \Exception($json['message']);
		}
	}
}