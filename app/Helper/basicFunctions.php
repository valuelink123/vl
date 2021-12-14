<?php
use App\Classes\SapRfcRequest;
use Illuminate\Support\Facades\DB;
/*
 * 该文件写的是一些基本常用的公共方法（例如几个功能中用到的获取相同的数据或者处理相同的数据）
 * 之前会存在多处取相同数据，现采用在此文件统一取数据源或者处理相同的数据，这样整合后方便后期的修改，修改一处即可，不用修改多处
 */
/*
 * 更新CRM数据信息（facebook_name和facebook_group）
 * 查client_info表中是否有此客户的数据，如若有就更新facebook_name和facebook_group字段数据，如若没有就插入客户信息数据到client和client_info表
 */
function updateCrm($data,$updateClient){
	$clientInfo = DB::table('client_info')->where('email',$data['email'])->orWhere('encrypted_email',$data['email'])->get(array('id'))->first();
	if(!empty($clientInfo)){
		$res = DB::table('client_info')->where('email',$data['email'])->orWhere('encrypted_email',$data['email'])->update($updateClient);
	}else{
		//通过sap获取订单信息
		try {
			//若有订单号，通过订单号获取基本信息
			if(isset($data['order_id']) && $data['order_id']){
				$sap = new SapRfcRequest();
				$sapOrderInfo = SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $data['order_id']]));
				$data['country'] = isset($data['country']) && $data['country'] ? $data['country'] : $sapOrderInfo['CountryCode'];
				$data['phone'] = isset($data['phone']) && $data['phone'] ? $data['phone'] : $sapOrderInfo['Phone'];
				$data['name'] = isset($data['name']) && $data['name'] ? $data['name'] : $sapOrderInfo['Name'];
				$data['processor'] = isset($data['processor']) && $data['processor'] ? $data['processor'] : 0;
				if(isset($sapOrderInfo['orderItems'][0]['ASIN']) &&($data['processor']==0 || empty($data['brand']))){
					$sql = "select t2.id as user_id,t1.brand as brand 
										from asin as t1
										left join users as t2 on t2.sap_seller_id = t1.sap_seller_id
										where asin = '{$sapOrderInfo['orderItems'][0]['ASIN']}' and site = 'www.{$sapOrderInfo['SalesChannel']}' and sellersku = '{$sapOrderInfo['orderItems'][0]['SellerSKU']}' limit 1";
					$userData = DB::select($sql);
					if($userData && isset($userData[0])){
						if($data['processor']==0){
							$data['processor'] = isset($userData[0]->user_id) ? $userData[0]->user_id : 0;
						}
						if(empty($data['brand'])){
							$data['brand'] = isset($userData[0]->brand) ? $userData[0]->brand : '';
						}
					}
				}
			}
		} catch (\Exception $e) {
			$data['amazon_order_id'] = '';
		}
		$insertInfo = array(
			'name'=>isset($data['name']) ? $data['name'] : '',
			'email'=>$data['email'],
			'encrypted_email' => md5($data['email']).rand(1000,9999).'@valuelinkltd.com',
			'phone'=>isset($data['phone']) ? $data['phone'] : '',
			'country'=>isset($data['country']) ? $data['country'] : '',
			'brand'=>isset($data['brand']) ? $data['brand'] : '',
			'from'=>isset($data['from']) ? $data['from'] : '',
		)+$updateClient;
		//插入到client表里的数据0
		$insertClient = array(
			'date'=>isset($data['date']) ? $data['date'] : date('Y-m-d H:i:s'),
			'created_at'=>date('Y-m-d H:i:s'),
			'updated_at'=>date('Y-m-d H:i:s'),
			'processor' => isset($data['processor']) ? $data['processor'] : 0,
		);

		$insertInfo['client_id'] = $res = DB::table('client')->insertGetId($insertClient);
		$res = DB::table('client_info')->insertGetId($insertInfo);
	}
	return $res;
}