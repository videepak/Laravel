<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Violation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'violation_reason', 'violation_action', 'building_id',
        'violation_image', 'user_id', 'barcode_id',
        'activity_id', 'status', 'special_note',
        'mobile_uniqe_id', 'type', 'property_id',
    ];

    public function getSpecialNoteAttribute($value)
    {
        return ucfirst($value);
    }

    public function getReason()
    {
        return $this->belongsTo('App\Reason', 'violation_reason')
                ->withTrashed();
    }

    public function getAction()
    {
        return $this->belongsTo('App\Action', 'violation_action')
                ->withTrashed();
    }

    public function getUser()
    {
        return $this->belongsTo('App\User', 'user_id')->withTrashed();
    }

    public function getUnitNumber()
    {
        return $this->belongsTo('App\Units', 'barcode_id', 'barcode_id')
                ->withTrashed();
    }

    public function getPropertyName()
    {
        return $this->hasManyThrough('App\Property', 'barcode_id', 'barcode_id')
                ->withTrashed();
    }

    public function getProperty()
    {
        return $this->hasOne('App\Property', 'id', 'property_id');
    }

    public function getBuilding()
    {
        return $this->hasOne('App\Building', 'id', 'building_id');
    }

    public function images()
    {
        return $this->hasMany('App\ViolationImages', 'violation_id');
    }

    public function getResidentDetail()
    {
        return $this->hasOne('App\ResidentsUnit','violation_id');
    }

    public function getPropertyDetail()
    {
        return $this->belongsTo('App\Property', 'property_id');
    }

    public function getBuildingDetail()
    {
        return $this->belongsTo('App\Building', 'building_id');
    }

    public function getActivityByBarcode()
    {
        return $this->hasMany('App\Activitylogs', 'barcode_id', 'barcode_id')
            ->latest();
    }
}
