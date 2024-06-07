<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteCheckIn extends Model
{
    use SoftDeletes;

    public function isRouteComplete()
    {
        return $this->hasMany('App\Activitylogs', 'barcode_id', 'barcode_id');
    }

    public function getProperty()
    {
        return $this->hasOne('App\Property', 'id', 'property_id');
    }

    public function getBuilding()
    {
        return $this->hasOne('App\Building', 'id', 'building_id');
    }

    public function getBuildingDetail()
    {
        return $this->belongsTo('App\Building', 'building_id');
    }

    public function getNameAttribute($value)
    {
        return ucwords($value);
    }
}
