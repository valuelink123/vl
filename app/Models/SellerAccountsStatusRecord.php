<?php

namespace App\Models;
use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use DB;

class SellerAccountsStatusRecord extends Model
{
	use  ExtendedMysqlQueries;
	protected $connection = 'vlz';
	protected $table = 'seller_accounts_status_record';

	public function getEnableAccountInfo()
	{
		$sql = "SELECT
		*
	FROM
		seller_accounts_status_record
	WHERE
		ID IN (
			SELECT
				max(id) AS id
			FROM
				seller_accounts_status_record
			GROUP BY
				mws_marketplaceid,
				mws_seller_id
		)
and account_status='ENABLE' and record_status='ENABLE'";
		$data = DB::connection('vlz')->select($sql);
		$data = json_decode(json_encode($data),true);
		$returnData = array();
		$site = getMarketDomain();//获取站点选项
		$siteDomain = array();
		foreach($site as $key=>$val){
			$siteDomain[$val->marketplaceid] = $val->domain;
		}
		foreach($data as $key=>$val){
			$val['site'] = isset($siteDomain[$val['mws_marketplaceid']]) ? $siteDomain[$val['mws_marketplaceid']] : $val['mws_marketplaceid'];
			$returnData[$val['site'].'_'.$val['mws_seller_id']] = $val;
		}
		return $returnData;
	}

}

