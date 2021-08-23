<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpcSbrandsTarget extends Model
{
    protected $guarded = [];
    

    public function setExpressionAttribute($value)
	{
        $this->attributes['expression'] = json_encode($value);
	}
	
	public function getExpressionAttribute($value)
	{
		return json_decode($value, true);
	}

    public function setResolvedExpressionAttribute($value)
	{
        $this->attributes['resolved_expression'] = json_encode($value);
	}
	
	public function getResolvedExpressionAttribute($value)
	{
		return json_decode($value, true);
	}
}
