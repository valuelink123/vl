<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.21
 * Time: 16:31
 */

namespace App\Http\Controllers\Frank\Models;

use Illuminate\Database\Eloquent\Model;

class KmsUserManual extends Model {
    protected $table = 'kms_user_manual';
    protected $fillable = ['item_group', 'item_model', 'link'];
}
