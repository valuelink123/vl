<?php
/**
 * Created by PhpStorm.
 * Date: 18.11.29
 * Time: 17:39
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CtgOrder extends Model {

    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'ctg_order';
    protected $guarded = [];

}
