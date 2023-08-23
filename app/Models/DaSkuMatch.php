<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaSkuMatch extends Model {

    protected $connection = 'amazon';
    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'da_sku_match';

}
