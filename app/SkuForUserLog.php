<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class SkuForUserLog extends Model
{
    //
	protected $connection = 'amazon';
    protected $table = 'sku_for_user_logs';
	
	protected $guarded = [];
    public $timestamps = true;

    const STATUS = ['1'=>'Confrim','-1'=>'Cannel'];
}
