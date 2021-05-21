<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformOrder extends Model {

    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];

    const SYNC = [
        '-1'=>'取消订单',
        '0'=>'创建订单',
        '1'=>'修改订单',
    ];


    const SYNC_STATUS = [
        '-2'=>'取消同步',
        '-1'=>'同步失败',
        '0'=>'等待同步',
        '1'=>'同步成功',
    ];


    const PLATFORM = [
        'ALIEXPRESS'=>'ALIEXPRESS',
        'AMAZON'=>'AMAZON',
        'WISH'=>'WISH',
        'EBAY'=>'EBAY',
        'BIGCOMMERCE'=>'BIGCOMMERCE',
        '3DCART'=>'3DCART',
        'WOOCOMMERCE'=>'WOOCOMMERCE',
        'MAGENTO'=>'MAGENTO',
        'CA'=>'CA',
        'WALMART'=>'WALMART',
        'OTHER'=>'OTHER',
    ];

    public function items():hasMany
    {
        return $this->hasMany(PlatformOrderItem::class, 'platform_order_id', 'id');
    }


    public function user():BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }
    

}
