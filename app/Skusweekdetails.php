<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Skusweekdetails extends Model
{
    //
	protected $connection = 'amazon';
    protected $table = 'asin_daily_report';
	protected $guarded = [];
    public $timestamps = false;
}
