<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ExtendedMysqlQueries;

class SapPurchase extends Model
{
    use  ExtendedMysqlQueries;

    protected $table = 'sap_purchase';

    protected $guarded = [];

    public $timestamps = false;

}