<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PlatformOrderItem extends Model {

    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];

    public function order():BelongsTo
    {
        return $this->belongsTo(PlatformOrder::class, 'id', 'platform_order_id');
    }
}
