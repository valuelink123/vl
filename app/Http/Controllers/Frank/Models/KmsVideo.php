<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.21
 * Time: 16:31
 */

namespace App\Http\Controllers\Frank\Models;

use Illuminate\Database\Eloquent\Model;

class KmsVideo extends Model {
    protected $table = 'kms_video';
    protected $fillable = ['item_group', 'item_model', 'type', 'descr', 'link'];
}
