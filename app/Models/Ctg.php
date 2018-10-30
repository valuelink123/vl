<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.21
 * Time: 16:31
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ctg extends Model {
    protected $table = 'ctg';
    protected $fillable = ['type', 'tag'];
}
