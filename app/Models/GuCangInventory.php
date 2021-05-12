<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class GuCangInventory extends Model {
    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];
}
