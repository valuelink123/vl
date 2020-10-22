<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class TransferTask extends Model {

    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];
    const STATUS = ['0'=>'资料提供中','1'=>'待采购','2'=>'换标中','3'=>'待出库','4'=>'已发货','5'=>'签收中','6'=>'签售完毕','7'=>'取消发货'];
}
