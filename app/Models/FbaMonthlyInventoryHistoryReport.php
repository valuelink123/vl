<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class FbaMonthlyInventoryHistoryReport extends Model
{
    protected $connection = 'amazon';
    protected $table = 'fba_monthly_inventory_history_report';
    const DISPOSITION = [
        'SELLABLE'=>'可售',
        'DEFECTIVE'=>'产品质量问题',
        'CUSTOMER_DAMAGED'=>'产品破损（买家原因）',
        'DISTRIBUTOR_DAMAGED'=>'产品破损（卖家原因）',
        'WAREHOUSE_DAMAGED'=>'产品破损（仓库原因）',
        'CARRIER_DAMAGED'=>'产品破损（物流原因）',
        'EXPIRED'=>'产品过期',
    ];
}
