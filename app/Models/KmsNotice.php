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
    protected $fillable = ['item_group', 'item_model', 'title', 'content'];
}
