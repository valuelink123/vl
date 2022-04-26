<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class SalesAlert extends Model
{
    //
    protected $table = 'sales_alert';
    public $timestamps = true;
	protected $guarded = [];
}
