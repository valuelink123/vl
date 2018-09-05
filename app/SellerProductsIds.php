<?php
/**
 * App\Models\SellerProductsIds
 *
 * @property integer $user_id
 * @property integer $seller_account_id
 * @property array $products ([[sku:'', asin:'', quantity: ''],[sku:'', asin:'', quantity: '']])
 **/


namespace App;

use Illuminate\Database\Eloquent\Model;

class SellerProductsIds extends Model
{
    protected $fillable = ['user_id', 'seller_account_id'];






}
