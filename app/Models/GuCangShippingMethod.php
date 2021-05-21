<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class GuCangShippingMethod extends Model {

    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];
    const TYPE = [
        '0'=>'尾程物流产品',
        '1'=>'退件代选物流产品',
        '2'=>'头程物流产品',
        '3'=>'退件自选物流产品',
        '4'=>'未预报退件物流产品',
        '5'=>'销毁物流产品',
        '6'=>'自提物流产品',
    ];
}
