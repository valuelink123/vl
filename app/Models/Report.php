<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Report extends Model
{
    protected $connection = 'amazon';
    protected $guarded = [];
    public $table='reports';
	
	public function requestReport():BelongsTo
    {
        return $this->belongsTo(CreatedReport::class, 'report_id', 'report_id');
    }
}
	
