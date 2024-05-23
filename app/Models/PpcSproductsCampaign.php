<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpcSproductsCampaign extends Model
{
    protected $guarded = [];
    

    public function setBiddingAttribute($value)
	{
        $this->attributes['bidding'] = json_encode($value);
	}
	
	public function getBiddingAttribute($value)
	{
		return json_decode($value, true);
	}
	
	public function setNetworksAttribute($value)
	{
        $this->attributes['networks'] = json_encode($value);
	}
	
	public function getNetworksAttribute($value)
	{
		return json_decode($value, true);
	}
}
