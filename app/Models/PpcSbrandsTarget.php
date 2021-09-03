<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpcSbrandsTarget extends Model
{
    protected $guarded = [];
    

    public function setExpressionsAttribute($value)
	{
        $this->attributes['expression'] = json_encode($value);
	}
	
	public function getExpressionsAttribute($value)
	{
		return json_decode($value, true);
	}

    public function setResolvedExpressionsAttribute($value)
	{
        $this->attributes['resolved_expression'] = json_encode($value);
	}
	
	public function getResolvedExpressionsAttribute($value)
	{
		return json_decode($value, true);
	}
}
