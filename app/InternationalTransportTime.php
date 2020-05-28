<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class InternationalTransportTime extends Model
{
    //
	protected $connection = 'amazon';
    protected $table = 'international_transport_time';
	
	protected $guarded = [];
    public $timestamps = false;
}
