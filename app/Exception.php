<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Exception extends Model
{
    //
    protected $table = 'exception';
    protected $guarded = [];
    public $timestamps = false;
}
