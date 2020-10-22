<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class TransferPlan extends Model {

    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];
    const STATUS = ['0'=>'制定','1'=>'已审核','2'=>'退回','3'=>'关闭'];
}
