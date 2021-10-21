<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\InventoryCycleCountReason;


class InventoryCycleCount extends Model {

	use  ExtendedMysqlQueries;
	public $timestamps = true;
	protected $table = 'inventory_cycle_count';
	protected $guarded = [];

	const STATUS = [
		1 => '未盘点',
		2 => '完成盘点',
		3 => '完成差异处理',
		4 => '完成确认'
	];

	public function reason()
	{
		return $this->hasMany(InventoryCycleCountReason::class, 'inventory_cycle_count_id', 'id');
	}

}
