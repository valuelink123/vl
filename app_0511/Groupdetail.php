<?php namespace App;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class Groupdetail extends Model {

    use  ExtendedMysqlQueries;
    protected $table = 'group_detail';
    public $timestamps = false;
    protected $guarded = [];

}
