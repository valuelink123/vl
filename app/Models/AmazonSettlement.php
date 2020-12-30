<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class AmazonSettlement extends Model
{
    protected $connection = 'amazon';
    protected $table = 'amazon_settlements';
}
