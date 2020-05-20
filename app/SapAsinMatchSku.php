<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class SapAsinMatchSku extends Model
{
    //
	protected $connection = 'amazon';
    protected $table = 'sap_asin_match_sku';
	
	protected $guarded = [];
    public $timestamps = false;
}
