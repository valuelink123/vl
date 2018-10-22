<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.21
 * Time: 16:31
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KmsTag extends Model {
    protected $table = 'kms_tag';
    protected $fillable = ['type', 'tag'];

    static function puts($type, $tags) {

        if (!is_array($tags)) {
            $tags = explode(',', $tags);
        }

        foreach ($tags as $tag) {
            static::updateOrCreate(compact('type', 'tag'));
        }
    }

    /**
     * 根据 $type 返回使用过的标签；
     * 如果 $type 为空则返回所有；
     * @param string $type
     * @return array
     */
    static function getTagList($type = '') {

        $rows = static::select('tag')->where(function ($model) use ($type) {
            if (!empty($type)) $model->where('type', $type);
        })->get();

        return array_map(function ($row) {
            // return $row->tag;
            return $row['tag'];
        }, $rows->toArray()); // toArray 之后，有时候里面还是对象，有时候又是数组，搞不懂；
    }
}
