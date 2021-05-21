<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class GuCangWarehouse extends Model {

    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];
}
