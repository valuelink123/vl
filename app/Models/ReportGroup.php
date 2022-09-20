<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ReportGroup extends Model
{
    protected $connection = 'amazon';
	public $timestamps = true;
    protected $guarded = [];
    public $table='report_groups';
	
	public function reports():hasMany
    {
        return $this->hasMany(CreatedReport::class, 'group_id', 'id');
    }
}
	
