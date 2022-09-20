<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class CreatedReport extends Model
{
    protected $connection = 'amazon';
	public $timestamps = true;
    protected $guarded = [];
    public $table='created_reports';
	
	public function group():BelongsTo
    {
        return $this->belongsTo(ReportGroup::class, 'id', 'group_id');
    }
	
	public function report():hasOne
    {
        return $this->hasOne(Report::class, 'report_id', 'report_id');
    }

}
