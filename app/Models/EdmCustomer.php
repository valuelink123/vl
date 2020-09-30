<?php

namespace App\Models;
use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class EdmCustomer extends Model
{
	use  ExtendedMysqlQueries;

	protected $table = 'edm_customer';
	// public $timestamps = false;//不用created_at和updated_at 字段
	// protected $fillable = ['id'];  //你插入数据到表里，必须要在这个数组里（好像只针对 create方法，其它的insert不受影响。
	// protected $guarded = ['id'];     //这个是保护修改时不能修改的字段，就是在插入前把数据循环一下如果在这里就unset掉了。


}

