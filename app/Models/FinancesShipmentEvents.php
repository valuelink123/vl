<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class FinancesShipmentEvents extends Model
{
    protected $connection = 'amazon';
    protected $table = 'finances_shipment_events';
}