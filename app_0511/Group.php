<?php namespace App;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class Group extends Model {

    use  ExtendedMysqlQueries;

    protected $table = 'group';
    public $timestamps = false;
    protected $guarded = [];


}
