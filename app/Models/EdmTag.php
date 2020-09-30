<?php

namespace App\Models;
use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class EdmTag extends Model
{
	use  ExtendedMysqlQueries;
	protected $table = 'edm_tag';

	//得到显示状态的选项数据(edm客户组别)
	public static function getEdmCustomerTag()
	{
		$_data = EdmTag::orderBy('id','desc')->get()->toArray();
		$data = array();
		foreach($_data as $key=>$val){
			$data[$val['id']] = $val['name'];
		}
		return $data;
	}

}

