<?php

namespace App\Models;
use App\Services\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use App\SapPurchase;
use App\SapShipment;
use App\SapAsinMatchSku;
use App\ShipmentRequest;
class AsinPlansPlan extends Model
{
	//
	use  ExtendedMysqlQueries;
	protected $table = 'asin_plans_plans';

	protected $guarded = [];
	public $timestamps = false;
}
