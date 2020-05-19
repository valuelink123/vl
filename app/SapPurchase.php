<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class SapPurchase extends Model
{
    //
	protected $connection = 'amazon';
    protected $table = 'sap_purchases';
	
	protected $guarded = [];
    public $timestamps = false;
}
