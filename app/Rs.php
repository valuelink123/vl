<?php namespace App;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class Rs extends Model {

    use  ExtendedMysqlQueries;

    protected $table = 'review_step';
    public $timestamps = false;
    protected $guarded = [];

}
