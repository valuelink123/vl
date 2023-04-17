<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class TransferPlan extends Model {

    use  ExtendedMysqlQueries;
    protected $connection = 'amazon';
    public $timestamps = true;
    protected $guarded = [];
    const STATUS = [
        '0'=>'取消调拨请求',
        '1'=>'BU经理审核',
        '2'=>'BG总经理审核',
        '3'=>'计划员审核',
        '4'=>'计划经理确认',
        '5'=>'物流确认',
        '6'=>'已审批',
    ];

    const SHIPMETHOD = [
        'other'=>'DA卡派',
        'amazon'=>'亚马逊自提',
    ];

    const TF = [
        '0'=>'NO',
        '1'=>'YES',
    ];

    const SHIPMENTSTATUS = [
        '0'=>'资料提供中',
        '1'=>'DA已创建出库单',
        '2'=>'换标中',
        '3'=>'待出库',
        '4'=>'已发货',
        '5'=>'DA系统订单已完成',
        '6'=>'物流确认运费',
        '7'=>'SAP创建调拨单',
        '8'=>'取消发货',
    ];

    const DASHIPMENTSTATUS = [
        '1'=>'DA已创建出库单',
        '2'=>'换标中',
        '3'=>'待出库',
        '4'=>'已发货',
        '5'=>'DA系统订单已完成',
        '8'=>'取消发货',
    ];


    const SHIPSHIPMENTSTATUS = [
        '5'=>'DA系统订单已完成',
        '6'=>'物流确认运费',
        '7'=>'SAP创建调拨单',
        '8'=>'取消发货',
    ];

    public function setItemsAttribute($value)
	{
        if (is_array($value)) {
            $this->attributes['items'] = json_encode($value);
        }
	}
	
	public function getItemsAttribute($value)
	{
		return json_decode($value, true);
	}

    public function setShipsAttribute($value)
	{
        if (is_array($value)) {
            $this->attributes['ships'] = json_encode($value);
        }
	}
	
	public function getShipsAttribute($value)
	{
		return json_decode($value, true);
	}
}
