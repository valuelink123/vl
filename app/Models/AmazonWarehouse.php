<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmazonWarehouse extends Model {

    protected $connection = 'amazon';
    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'amazon_warehouses';

}
