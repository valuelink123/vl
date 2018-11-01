<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.21
 * Time: 16:31
 */

namespace App\Models;

use App\Exceptions\HypocriteException;
use Illuminate\Database\Eloquent\Model;

class Ctg extends Model {
    protected $table = 'ctg';
    protected $fillable = ['order_id', 'product_asin', 'product_sku', 'gift_sku', 'name', 'email', 'phone', 'address', 'rating', 'note'];

    /**
     * @throws HypocriteException
     */
    public static function add($row) {
        try {
            return self::create($row);
        } catch (\Exception $e) {
            return HypocriteException::wrap($e, 'Your ORDER ID has been used. For help, please mail to support@claimthegift.com');
        }
    }
}
