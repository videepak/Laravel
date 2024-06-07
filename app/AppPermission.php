<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppPermission extends Model
{
    protected $fillable = [
        'manual_pickup', 'subscriber_id', 'user_id',
        'recycling_collected', 'units_serviced',
        'violation', 'checkin_pending', 'daliy_task_complete'
    ];
}
