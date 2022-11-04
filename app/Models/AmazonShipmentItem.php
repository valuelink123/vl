<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class AmazonShipmentItem extends Model
{
    protected $connection = 'amazon';
	public $timestamps = false;
    protected $guarded = [];
    public $table='amazon_shipment_items';
	const STATUS = ['0'=>'销售开case','1'=>'物流提供POD/ISA','2'=>'索赔失败','3'=>'索赔成功','4'=>'无需索赔'];

}
