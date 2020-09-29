<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class SkuForUser extends Model
{
    //
	protected $connection = 'amazon';
    protected $table = 'sku_for_user';
	
	protected $guarded = [];
    public $timestamps = false;
}
