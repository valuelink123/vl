<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Classes\CurlRequest;
use DB;
use App\User;

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

	/*
	 * 得到验证码方法
	 * 从服务器端拉取验证码
	 */
	public function getCode()
	{
		header('Access-Control-Allow-Origin:*');
		$userId = isset($_COOKIE['userId']) && $_COOKIE['userId'] ? $_COOKIE['userId'] : 0;
		//获取验证码
		session_start();//开启session记录验证码数据
		$num = 4;
		$size = 20;
		$width = $height = 0;

		//vCode 字符数目，字体大小，图片宽度、高度
		!$width && $width = $num * $size * 4 / 5 + 15;
		!$height && $height = $size + 10;

		//设置验证码字符集合
		$str = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVW";
		//保存获取的验证码
		$code = '';

		//随机选取字符
		for ($i = 0; $i < $num; $i++) {
			$code .= $str[mt_rand(0, strlen($str)-1)];
		}

		//创建验证码画布
		$im = imagecreatetruecolor($width, $height);

		//背景色
		$back_color = imagecolorallocate($im, mt_rand(0,100),mt_rand(0,100), mt_rand(0,100));

		//文本色
		$text_color = imagecolorallocate($im, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));

		imagefilledrectangle($im, 0, 0, $width, $height, $back_color);


		// 画干扰线
		for($i = 0;$i < 5;$i++) {
			$font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			imagearc($im, mt_rand(- $width, $width), mt_rand(- $height, $height), mt_rand(30, $width * 2), mt_rand(20, $height * 2), mt_rand(0, 360), mt_rand(0, 360), $font_color);
		}

		// 画干扰点
		for($i = 0;$i < 50;$i++) {
			$font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $font_color);
		}

		//随机旋转角度数组
		$array=array(5,4,3,2,1,0,-1,-2,-3,-4,-5);

		// 输出验证码
//		 imagefttext(image, size, angle, x, y, color, fontfile, text);
		imagestring($im, 5, 25, 8, $code, $text_color);
//		@imagefttext($im, $size , array_rand($array), 12, $size + 6, $text_color, 'c:\WINDOWS\Fonts\simsun.ttc', $code);
		$_SESSION["VerifyCode_".$userId]=$code;
		//no-cache在每次请求时都会访问服务器
		//max-age在请求1s后再次请求会再次访问服务器，must-revalidate则第一发送请求会访问服务器，之后不会再访问服务器
		// header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
//		header("Cache-Control: no-cache");
		header("Content-type: image/png;charset=gb2312");
		//将图片转化为png格式
		imagepng($im);
		imagedestroy($im);
	}
	/*
	 * 弹出验证框
	 */
	public function alertRemind()
	{
		header('Access-Control-Allow-Origin:*');
		$userId = isset($_COOKIE['userId']) && $_COOKIE['userId'] ? $_COOKIE['userId'] : 0;
		$result['status'] = 0;
		$userRole = User::find($userId)->roles->pluck('id')->toArray();
		if($userId && (in_array(12,$userRole) || in_array(13,$userRole))){
			saveOperationLog('verifyCode', 0, array());//操作插入日志表中
			$result['status'] = 1;
		}
		$result['userId']=$userId;
		echo json_encode($result);
	}

	/*
	 * 验证填写的验证码是否正确
	 * 验证验证码是否输入正确
	 */
	public function verifyCode()
	{
		header('Access-Control-Allow-Origin:*');
		session_start();//开启session获取验证码数据
		$userId = isset($_COOKIE['userId']) && $_COOKIE['userId'] ? $_COOKIE['userId'] : 0;
		$code = isset($_REQUEST['code']) && $_REQUEST['code'] ? trim($_REQUEST['code']) : '';
		$trueCode = $_SESSION["VerifyCode_".$userId];
		$result['status'] = 0;
		if($code == $trueCode){
			$result['status'] = 1;
		}
		$result['userId'] = $userId;
		saveOperationLog('verifyCode', 0, array('code'=>$code));//操作插入日志表中
		echo json_encode($result);
	}
}