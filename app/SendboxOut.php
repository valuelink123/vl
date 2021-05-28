<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ExtendedMysqlQueries;
class SendboxOut extends Model
{
    use  ExtendedMysqlQueries;
    //
    protected $table = 'sendbox_out';
    public $timestamps = true;
}
