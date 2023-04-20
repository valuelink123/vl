<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TransferPlanItemShip extends Model {

    use  ExtendedMysqlQueries;
    protected $connection = 'amazon';
    public $timestamps = true;
    protected $guarded = [];
    protected $fillable = [ 'id','transfer_plan_item_id','sku','location','quantity','broads','packages'];

    public function item():BelongsTo
    {
        return $this->belongsTo(TransferPlanItem::class, 'id', 'transfer_plan_item_id');
    }
}
