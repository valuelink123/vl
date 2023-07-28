<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkuSize extends Model {

    protected $connection = 'amazon';
    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'sku_size';

}
