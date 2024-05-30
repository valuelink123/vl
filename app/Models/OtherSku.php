<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherSku extends Model {
    protected $connection = 'amazon';
    public $timestamps = false;
    protected $guarded = [];
}
