<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class AmazonSettlementDetail extends Model
{
    protected $connection = 'amazon';
    protected $table = 'amazon_settlement_details';
}
