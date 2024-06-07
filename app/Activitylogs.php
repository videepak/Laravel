<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Builder\MyBuilder;

class Activitylogs extends Model
{

    use SoftDeletes;

    protected $table = 'activity_log';
    protected $fillable = [
       'text',
        'user_id', 'wast', 'recycle',
        'updated_by', 'barcode_id',
        'type', 'latitude', 'longitude',
        'ip_address', 'property_id', 'building_id'
    ];

    const VIOLATION = 3;
    const TASKCOMPLETE = 13;
    const CHECKPOINT = 11;
    const CHECKINOUT = 7;

    public function taskMedia()
    {
        return $this->hasOne('App\TaskImage', 'activity_id');
    }

    public function logs()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function unit()
    {
        return $this->belongsTo('App\Units', 'barcode_id', 'barcode_id');
    }

    public function note()
    {
        return $this->hasOne('App\BarcodeNotes', 'activityLogId', 'id');
    }

    public function getUserDetail()
    {
        return $this->hasOne('App\User', 'id', 'user_id')->withTrashed();
    }

    public function getProperty()
    {
        return $this->hasOne('App\Property', 'id', 'property_id');
    }

    public function getUserDetailUpdatedBy()
    {
        return $this->hasOne('App\User', 'id', 'updated_by')->withTrashed();
    }

    public function getPropertyDetailByPropertyId()
    {
        return $this->hasOne('App\Property', 'id', 'property_id');
    }

    public function getPropertyDetailByPropertyIdWithTrashed()
    {
        return $this->hasOne('App\Property', 'id', 'property_id')
                ->withTrashed();
    }

    public function getBuildingDetailWithTrashed()
    {
        return $this->hasOne('App\Building', 'id', 'building_id')
                ->withTrashed();
    }

    public function getBuilding()
    {
        return $this->hasOne('App\Building', 'id', 'building_id');
    }
}
