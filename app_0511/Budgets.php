<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Budgets extends Model
{
    //
    protected $table = 'budgets';
	protected $guarded = [];
    public $timestamps = true;
}
