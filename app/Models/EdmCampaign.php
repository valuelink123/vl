<?php

namespace App\Models;
use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class EdmCampaign extends Model
{
	use  ExtendedMysqlQueries;

	protected $table = 'edm_campaign';

}

