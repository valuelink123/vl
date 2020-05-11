<?php

namespace App;
use App\Services\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class Budgetdetails extends Model
{
    //
	use  ExtendedMysqlQueries;
    protected $table = 'budget_details';
	protected $guarded = [];
    public $timestamps = true;
}
