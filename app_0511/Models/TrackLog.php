<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TrackLog extends Model
{
    protected $table = 'track_log';
    public $timestamps = false;
    protected $guarded = [];

    //添加数据
    public function add($data)
    {
        $userInfo = Auth::user();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['processor'] = $userInfo->id;
        return $this->create($data);
    }

}