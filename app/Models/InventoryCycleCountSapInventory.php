<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;


class InventoryCycleCountSapInventory extends Model {

	use  ExtendedMysqlQueries;
	public $timestamps = true;
	protected $table = 'inventory_cycle_count_sapinventory';
	protected $guarded = [];

	const TYPE = [
		'BEFORE' => '处理前数据',
		'AFTER' => '处理后数据',
	];
}
