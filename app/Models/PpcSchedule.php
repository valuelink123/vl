<?php
namespace App\Models;
use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpcSchedule extends Model
{
    use  ExtendedMysqlQueries;
    protected $guarded = [];

    const STATUS = [
        '1'=>'Activated',
        '0'=>'Unactivated',
    ];

    const TYPE = [
        'campaign'=>'Campaign',
        'adGroup'=>'AdGroup',
        'target'=>'Target',
        'keyword'=>'Keyword',
    ];

    public function user():BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }
	
	public function campaign():BelongsTo
    {
        return $this->belongsTo(\App\Models\PpcSproductsCampaign::class, 'campaign_id', 'campaign_id');
    }
	
	public function profile():BelongsTo
    {
        return $this->belongsTo(\App\Models\PpcProfile::class, 'profile_id', 'profile_id');
    }

}
