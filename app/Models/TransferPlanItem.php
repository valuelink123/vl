<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TransferPlanItem extends Model {

    use  ExtendedMysqlQueries;
    protected $connection = 'amazon';
    public $timestamps = true;
    protected $guarded = [];
    protected $with = ['ships'];
    protected $fillable = [ 
        'id', 
        'transfer_plan_id',
        'image',
        'seller_id',
        'asin',
        'sellersku',
        'sku',
        'fnsku',
        'quantity',
        'warehouse_code',
        'ship_fee',
        'rms',
        'rcard',
        'broads',
        'packages'
    ];

    public function plan():BelongsTo
    {
        return $this->belongsTo(TransferPlan::class, 'id', 'transfer_plan_id');
    }

    public function ships():hasMany
    {
        return $this->hasMany(TransferPlanItemShip::class, 'transfer_plan_item_id', 'id');
    }
}
