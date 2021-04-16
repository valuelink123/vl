<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RsgRequest extends Model {
    protected $guarded = [];
	public $timestamps = true;
	
	
	public function logs()
    {
        return $this->hasMany(RsgRequestLog::class, 'request_id', 'id');
    }
}
