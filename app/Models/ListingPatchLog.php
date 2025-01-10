<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ExtendedMysqlQueries;
class ListingPatchLog extends Model {
    use  ExtendedMysqlQueries;
    protected $connection = 'amazon';
    public $timestamps = true;
    protected $guarded = [];
}
