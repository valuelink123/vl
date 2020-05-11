<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.21
 * Time: 16:31
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KmsNotice extends Model {
    protected $table = 'kms_notice';
    protected $fillable = ['item_group', 'item_model', 'brand', 'tags', 'title', 'content'];

    public static function add($row) {
        if (!empty($row['id'])) {
            $find['id'] = $row['id'];
            return self::updateOrCreate($find, $row);
        } else {
            // static 和 self
            // 无继承关系时，两者无区别
            // 有继承关系时，self 指向代码调用所在类，static 指向当前类(继承链的末端)
            return static::create($row);
        }
    }
}
