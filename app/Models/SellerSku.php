<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ExtendedMysqlQueries;
class SellerSku extends Model {
    use  ExtendedMysqlQueries;
    protected $connection = 'amazon';
    public $timestamps = false;
    protected $guarded = [];
}
