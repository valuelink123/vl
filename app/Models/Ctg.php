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
    // protected $guarded = ['id']; // 黑名单模式
    protected $fillable = ['order_id', 'gift_sku', 'name', 'email', 'phone', 'address', 'note', 'rating'];

    /**
     * @throws HypocriteException
     */
    public static function add($row) {

        if (empty($row['order_id'])) throw new HypocriteException('ORDER ID IS UNSET');

        $row = array_map('trim', $row);

        try {
            return self::create($row);
        } catch (\Exception $e) {
            throw new HypocriteException('ORDER ID is Duplicate, You may had submitted it successfully. For help, please mail to support@claimthegift.com');
            // return HypocriteException::wrap($e, 'ORDER ID is Duplicate, You may had submitted it successfully. For help, please mail to support@claimthegift.com');
        }
    }
}
