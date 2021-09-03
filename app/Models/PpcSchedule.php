<?php
namespace App\Models;
use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpcSchedule extends Model
{
    use  ExtendedMysqlQueries;
    protected $guarded = [];

    const STATUS = [
        '1'=>'Activated',
        '0'=>'Unactivated',
    ];

    const TYPE = [
        'campaign'=>'Campaign',
        'adGroup'=>'AdGroup',
        'target'=>'Target',
        'keyword'=>'Keyword',
    ];

    public function user():BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }

}
