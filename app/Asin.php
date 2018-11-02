<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Asin extends Model
{
    //
    protected $table = 'asin';
	protected $guarded = [];
    public $timestamps = false;
}
