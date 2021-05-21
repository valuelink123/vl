<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformSku extends Model {

    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];

    public function user():BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }
}
