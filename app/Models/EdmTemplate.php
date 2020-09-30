<?php

namespace App\Models;
use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class EdmTemplate extends Model
{
	use  ExtendedMysqlQueries;

	protected $table = 'edm_template';

	//得到显示状态的选项数据(edm客户组别)
	public static function getEdmTemplateIdName()
	{
		$_data = EdmTemplate::orderBy('id','desc')->get()->toArray();
		$data = array();
		foreach($_data as $key=>$val){
			$data[$val['id']] = $val['name'];
		}
		return $data;
	}

}

