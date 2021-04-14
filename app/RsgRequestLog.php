<?php

namespace App;
use App\Services\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
class RsgRequestLog extends Model
{
    //
	use  ExtendedMysqlQueries;
	protected $connection = 'amazon';
    protected $table = 'rsg_requests_log';
	
	protected $guarded = [];
    public $timestamps = false;
}
