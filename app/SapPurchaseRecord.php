<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class SapPurchaseRecord extends Model
{
    //
	protected $connection = 'amazon';
    protected $table = 'sap_purchase_records';
	
	protected $guarded = [];
    public $timestamps = false;
}
