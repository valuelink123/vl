<?php

namespace App;
use App\Services\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
class DailyStatistic extends Model
{
    //
	use  ExtendedMysqlQueries;
	protected $connection = 'amazon';
    protected $table = 'daily_statistics';
	
	protected $guarded = [];
    public $timestamps = false;
}
