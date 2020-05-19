<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class SapShipment extends Model
{
    //
	protected $connection = 'amazon';
    protected $table = 'sap_shipments';
	
	protected $guarded = [];
    public $timestamps = false;
}
