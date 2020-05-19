<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class ShipmentRequest extends Model
{
    //
	protected $connection = 'amazon';
    protected $table = 'shipment_requests';
	
	protected $guarded = [];
    public $timestamps = false;
}
