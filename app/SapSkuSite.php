<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class SapSkuSite extends Model
{
    //
	protected $connection = 'amazon';
    protected $table = 'sap_sku_sites';
	
	protected $guarded = [];
    public $timestamps = false;
}
