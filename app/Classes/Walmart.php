<?php
namespace App\Classes;
ini_set("display_errors", "On");//打开错误提示
ini_set("error_reporting",E_ALL);//显示所有错误
use DB;


/**
 * walmart API
 */
class Walmart
{

	public function getOrderShipped($account_info,$params){
		$orderList = $this->walmartApi('get','all_orders',$account_info,$params);
		return isset($orderList['list']) ? $orderList['list'] : array();
	}

	//沃尔玛API
	public function walmartApi($curl,$type,$account_info,$data = array()){
		$url = "https://marketplace.walmartapis.com";

//		$url = "https://sandbox.walmartapis.com";
		//调取数据库数据
		$client_id = $account_info->account_client_id;
		$client_secret = $account_info->account_client_secret;
		//常规参数
		$uniqid = uniqid();
		$authorization_key = base64_encode($client_id.":".$client_secret);
		$header = array();
		$header[] = "WM_SVC.NAME:Walmart Marketplace";
		$header[] = "WM_QOS.CORRELATION_ID:{$uniqid}";
		$header[] = "Authorization: Basic {$authorization_key}";
		$header[] = "Accept: application/json";
		$header[] = "Content-Type: application/x-www-form-urlencoded";
		$param=array(
			'grant_type'=>'client_credentials',
		);
		$strData = '';

		if($type == 'token'){
			$url .= "/v3/token";
			$strData = "grant_type=client_credentials";
			$res = $this->httpRequest('post',$url,$header,$strData);
			if(isset($res['access_token']))
				return $res['access_token'];
			else
				die("获取token错误，终止程序!");
		}

		$token = $this->walmartApi('post','token',$account_info);
		$header[] = "WM_SEC.ACCESS_TOKEN:{$token}";

		if($type == 'all_orders'){
			$url.= "/v3/orders";
		}

		if($curl == 'get'){
			if($data){
				$strurl = http_build_query($data);
				$url.="?{$strurl}";
			}
		}

		if($curl == 'post'){
			if($data)
				$strData = json_encode($data);
		}

		return $this->httpRequest($curl,$url,$header,$strData);
	}

	/**
	 * curl 请求
	 * @param $type
	 * @param $url
	 * @param array $header
	 * @param $data
	 * @return mixed
	 */
	function httpRequest($type, $url, array $header, $data)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if ($type != 'get') {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$output = curl_exec($curl);
		if ($output === false) {
			echo 'Curl error: ' . curl_error($curl);
			exit;
		}

		return json_decode($output, true);

	}

}




?>