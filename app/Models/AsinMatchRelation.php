<?php

namespace App\Models;
use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class AsinMatchRelation extends Model
{
	use  ExtendedMysqlQueries;
	protected $table = 'asin_match_relation';

	//得到物料对照关系的所有数据
	public static function getAsinMatchRelationData()
	{
		$_data = AsinMatchRelation::orderBy('id','desc')->get()->toArray();
		$data = array();
		foreach($_data as $key=>$val){
			$data[$val['id']] = $val;
		}
		return $data;
	}
}

