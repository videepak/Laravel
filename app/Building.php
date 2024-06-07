<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use SoftDeletes;

    protected $fillable = ['building_name', 'unit_number', 'property_id', 'address'];

    public function property()
    {
        return $this->belongsTo('App\Property', 'property_id', 'id');
    }

    public function getUnit()
    {
        return $this->hasMany('App\Units', 'building_id', 'id');
    }

    public function getWalkThrough()
    {
        return $this->hasMany('App\walkThroughRecord', 'building_id', 'id');
    }

    public function getRouteCheckPoint()
    {
        return $this->hasMany('App\RouteCheckIn', 'building_id', 'id');
    }
}
