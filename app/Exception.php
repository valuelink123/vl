<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\GiftCard;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Exception extends Model
{
    //
    protected $table = 'exception';
    protected $guarded = [];
    public $timestamps = false;


    public function giftcard():HasOne
    {
        return $this->hasOne(GiftCard::class, 'exception_id', 'id');
    }
}
