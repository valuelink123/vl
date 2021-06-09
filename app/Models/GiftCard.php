<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCard extends Model {

    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];

    const STATUS = [
        '0'=>'Actived',
        '1'=>'Used',
    ];


    public function exception():BelongsTo
    {
        return $this->belongsTo(\App\Exception::class, 'exception_id', 'id');
    }


    public function user():BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }
    

}
