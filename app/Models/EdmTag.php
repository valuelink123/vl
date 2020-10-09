<?php

namespace App\Models;
use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class EdmTag extends Model
{
	use  ExtendedMysqlQueries;
	protected $table = 'edm_tag';

	//得到标签的选项数据(ID=>Name)
	public static function getEdmCustomerTag()
	{
		$_data = EdmTag::orderBy('id','desc')->get()->toArray();
		$data = array();
		foreach($_data as $key=>$val){
			$data[$val['id']] = $val['name'];
		}
		return $data;
	}
	////得到标签的选项数据(ID=>mailchimp_tagid)
	public static function getEdmMailchimpTag()
	{
		$_data = EdmTag::orderBy('id','desc')->get()->toArray();
		$data = array();
		foreach($_data as $key=>$val){
			$data[$val['id']] = $val['mailchimp_tagid'];
		}
		return $data;
	}

}

