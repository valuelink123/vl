<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuCangOrder extends Model {

    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];


    public function items():hasMany
    {
        return $this->hasMany(GuCangOrderItem::class, 'order_code', 'order_code');
    }

    public function fee_details():hasMany
    {
        return $this->hasMany(GuCangOrderFee::class, 'order_code', 'order_code');
    }

    public function orderBoxInfo():hasMany
    {
        return $this->hasMany(GuCangOrderBox::class, 'order_code', 'order_code');
    }

    public function setFeeSummeryAttribute($value)
	{
        if (is_array($value)) {
            $this->attributes['fee_summery'] = json_encode($value);
        }
	}
	
	public function getFeeSummeryAttribute($value)
	{
		return json_decode($value, true);
	}

    

}
