<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SkusBase extends Model
{
    //
    protected $table = 'skus_base';
	protected $guarded = [];
    public $timestamps = false;
}
