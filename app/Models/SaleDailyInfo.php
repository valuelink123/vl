<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class SaleDailyInfo extends Model {
    use  ExtendedMysqlQueries;
    public $timestamps = false;
    protected $guarded = [];
    public $table='sale_daily_info';
}
