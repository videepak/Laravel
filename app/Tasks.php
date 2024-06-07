<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tasks extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'notify_property_manager', 'description', 'start_date', 'end_date', 'is_photo', 'frequency', 'user_id'
    ];

    public function getUser()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function property()
    {
        return $this->belongsToMany('App\Property', 'task_assigns', 'task_id', 'property_id');
    }

    public function taskImage()
    {
        return $this->hasMany('App\TaskImage', 'task_id');
    }

    public function activity()
    {
        return $this->hasMany('App\Activitylogs', 'barcode_id', 'barcode_id');
    }

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = \Carbon\Carbon::parse($value)
            ->addHour(6);
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = \Carbon\Carbon::parse($value)
            ->addDays(1)->addHour(5)->addMinutes(59)->addSeconds(59);
    }

    public function getStartDateAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('m/d/Y');
    }

    public function getEndDateAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->subDays(1)->format('m/d/Y');
    }
}
