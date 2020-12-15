<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Classes\CurlRequest;
use DB;
use App\User;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;

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
	 * 弹出验证框
	 */
	public function alertRemind(Request $request)
	{
		header('Access-Control-Allow-Origin:*');
		session_start();//开启session获取验证码数据
		$result['status'] = 0;
		$result['userId'] = 0;
		$email = isset($_REQUEST['email']) && $_REQUEST['email'] ? $_REQUEST['email'] : '';
		$password = isset($_REQUEST['password']) && $_REQUEST['password'] ? $_REQUEST['password'] : '';

		$auth = new LoginController();
		$res = $auth->attemptLogin($request, $request);
		$result['session_id'] = session_id();
		if($res){
			$userId = User::where('email',$email)->pluck('id')->first();
			$result['userId']=$userId;
			setcookie("userId", $result['userId']);
			$userRole = User::find($userId)->roles->pluck('id')->toArray();
			if(in_array(12,$userRole) || in_array(13,$userRole)){//用户角色是客服角色才可以,客服角色id为12和13
				saveOperationLog('alertRemind', 0, array('userId'=>$userId));//操作插入日志表中
				$result['status'] = 1;
				$result['msg'] = '登录成功';
			}else{
				$result['msg'] = '用户没有权限';
			}
		}else{
			$result['msg'] = '账号密码错误';
		}
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
//		$pid = isset($_REQUEST['pid']) && $_REQUEST['pid'] ? trim($_REQUEST['pid']) : '';
//		$trueCode = isset($_SESSION["VerifyCode_".$userId.'_'.$pid]) ? $_SESSION["VerifyCode_".$userId.'_'.$pid] : '';
		$trueCode = isset($_SESSION["VerifyCode_".$userId]) ? $_SESSION["VerifyCode_".$userId] : '';
		$result['status'] = 0;
		$result['code'] = $code;
		$result['truecode'] = $trueCode;
		if($code == $trueCode){
			$result['status'] = 1;
		}
		$result['userId'] = $userId;
		saveOperationLog('verifyCode', 0, array('userId'=>$userId,'code'=>$code,'truecode'=>$trueCode,'sessionkey'=>"VerifyCode_".$userId,'sessionid'=>session_id()));//操作插入日志表中
		echo json_encode($result);
	}
}