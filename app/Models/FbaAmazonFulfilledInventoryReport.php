<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class FbaAmazonFulfilledInventoryReport extends Model
{
    protected $connection = 'amazon';
    protected $table = 'fba_amazon_fulfilled_inventory_report';
    const STATUS = [
        'SELLABLE'=>'可售',
        'UNSELLABLE'=>'不可售',
    ];
}
