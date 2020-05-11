<?php namespace App;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class Sellertab extends Model {

    use  ExtendedMysqlQueries;

    protected $table = 'seller_asins_rules';
    public $timestamps = false;
    protected $guarded = [];


}
