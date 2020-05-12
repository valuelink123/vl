<?php

namespace App;
use App\Services\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
class AsinSalesPlan extends Model
{
    //
	use  ExtendedMysqlQueries;
	protected $connection = 'amazon';
    protected $table = 'asin_sales_plans';
	
	protected $guarded = [];
    public $timestamps = false;
}
