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
		$host = '192.168.10.10:18003';

		$url = "http://{$host}/rfc_site.php?appid={$appid}&method={$method}&orderId={$orderId}&sign={$sign}";
		// $url = "http://192.168.10.10:18003/rfc_site.php?appid=site0001&method=getOrder&orderId=113-5687773-9463408&sign=A04B065C3ABFB819E4200A8CBF4729C538EDA5DB";
		try {
			$json = CurlRequest::curl_get_contents($url);
			return $json;
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}
}