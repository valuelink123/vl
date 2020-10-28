<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class TransferRequest extends Model {

    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];
    const STATUS = ['0'=>'申请','1'=>'BU已审核','2'=>'BG已审核','3'=>'BU退回','4'=>'BG退回','5'=>'计划退回','6'=>'计划确认','7'=>'关闭','8'=>'计划已生成'];
}