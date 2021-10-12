<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;


class InventoryCycleCountReason extends Model {

	use  ExtendedMysqlQueries;
	public $timestamps = true;
	protected $table = 'inventory_cycle_count_reason';
	protected $guarded = [];

	const STATUS = [
		1 => '待处理',
		2 => '已处理',
	];
	const REASON = [
		1 => '原因1',
		2 => '原因2',
		3 => '原因3',
		4 => '原因4',
		5 => '原因5',
	];


}
