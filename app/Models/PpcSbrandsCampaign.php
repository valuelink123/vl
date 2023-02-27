<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpcSbrandsCampaign extends Model
{
    protected $guarded = [];

    public function setCreativeAttribute($value)
	{
        $this->attributes['creative'] = json_encode($value);
	}
	
	public function getCreativeAttribute($value)
	{
		return json_decode($value, true);
	}

    public function setLandingPageAttribute($value)
	{
        $this->attributes['landing_page'] = json_encode($value);
	}
	
	public function getLandingPageAttribute($value)
	{
		return json_decode($value, true);
	}

	public function setBidAdjustmentsAttribute($value)
	{
        $this->attributes['landing_page'] = json_encode($value);
	}
	
	public function getBidAdjustmentsAttribute($value)
	{
		return json_decode($value, true);
	}
}
