<?php

namespace App\Models;
use App\Services\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
class Asins extends Model
{
	use  ExtendedMysqlQueries;
	protected $connection = 'amzspider';
	protected $table = 'asins';

	protected $guarded = [];
	public $timestamps = false;
}
