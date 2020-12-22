<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class FbaInventoryAdjustmentsReport extends Model
{
    protected $connection = 'amazon';
    protected $table = 'fba_inventory_adjustments_report';
    const DISPOSITION = [
        'SELLABLE'=>'可售',
        'DEFECTIVE'=>'产品质量问题',
        'CUSTOMER_DAMAGED'=>'产品破损（买家原因）',
        'DISTRIBUTOR_DAMAGED'=>'产品破损（卖家原因）',
        'WAREHOUSE_DAMAGED'=>'产品破损（仓库原因）',
        'CARRIER_DAMAGED'=>'产品破损（物流原因）',
        'EXPIRED'=>'产品过期',
    ];
    const REASON = [
        '6' => '物流原因',
        '7' => '产品过期',
        'E' => '其它',
        'H' => '买家原因',
        'K' => '其它',
        'U' => '卖家原因',
        'D' => '丢弃',
        'F' => '找到库存',
        'N' => '找到库存',
        'M' => '误放库存',
        '5' => '误放库存',
        '3' => '商品重新定义',
        '4' => '商品重新定义',
        'O' => '库存调整',
        'P' => '盘盈',
        'Q' => '盘亏',
    ];

    const REASONSEARCH = [
        '6' => '物流原因',
        '7' => '产品过期',
        'E' => '其它',
        'H' => '买家原因',
        'K' => '其它',
        'U' => '卖家原因',
        'D' => '丢弃',
        'F' => '找到库存',
        'M' => '误放库存',
        '3' => '商品重新定义',
        'O' => '库存调整',
        'P' => '盘盈',
        'Q' => '盘亏',
    ];

    const REASONMATCH = [
        '6' => ['6'],
        '7' => ['7'],
        'E' => ['E'],
        'H' => ['H'],
        'K' => ['K'],
        'U' => ['U'],
        'D' => ['D'],
        'F' => ['F','N'],
        'M' => ['M','5'],
        '3' => ['3','4'],
        'O' => ['O'],
        'P' => ['P'],
        'Q' => ['Q'],
    ];

    
}


