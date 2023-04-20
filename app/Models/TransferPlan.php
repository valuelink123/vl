<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransferPlan extends Model {

    use  ExtendedMysqlQueries;
    protected $connection = 'amazon';
    public $timestamps = true;
    protected $guarded = [];
    protected $with = ['items'];
    protected $fillable = [ 
        'id','sap_seller_id','seller_id','marketplace_id','in_factory_code','out_factory_code','received_date','shipment_id','reservation_id','ship_method','ship_fee','ship_date','reson','tstatus','status','bg','bu','remark','files','da_order_id','reservation_date','sap_tm','sap_dn','sap_st0','broads','packages','api_msg'
    ];

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

    public function items():hasMany
    {
        return $this->hasMany(TransferPlanItem::class, 'transfer_plan_id', 'id');
    }

}
