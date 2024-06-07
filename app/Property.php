<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Property extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'resident_alert'
    ];

    const DISABLEAlERT = 0;
    const ENABLEAlERT = 1;

    public function task()
    {
        return $this->belongsToMany('App\Tasks', 'task_assigns', 'property_id', 'task_id');
    }

    public function service()
    {
        return $this->hasOne('App\Service', 'property_id');
    }

    public function getCustomer()
    {
        return $this->hasOne('App\Customer', 'id', 'customer_id');
    }

    public function exculdeProperty()
    {
        return $this->hasMany('App\ExcludedProperty', 'property_id', 'id');
    }

    // public function getRouteCheckPoint()
    // {
    //     return $this->hasMany('App\RouteCheckIn', 'property_id', 'id');
    // }
    
    public function getRedundantService()
    {
        return $this->hasMany('App\RedundantRouteService', 'property_id', 'id');
    }

    public function getEmployee()
    {
        return $this->belongsToMany('App\User', 'user_properties', 'property_id', 'user_id')
            ->whereNull('user_properties.deleted_at')
            ->where('user_properties.type', 1);
    }

    public function getPropertyManger()
    {
        return $this->belongsToMany('App\User', 'user_properties', 'property_id', 'user_id')
            ->whereNull('user_properties.deleted_at')
            ->where('user_properties.type', 2);
    }

    public function getState()
    {
        return $this->hasOne('App\State', 'id', 'state');
    }

    public function checkInProperty()
    {
        return $this->hasOne('App\PropertiesCheckIn', 'property_id');
    }

    public function allcheckIn()
    {
        return $this->hasMany('App\PropertiesCheckIn', 'property_id');
    }
    
    public function redundantServiceInOut()
    {
        return $this->hasMany('App\RedundantServiceInOut', 'property_id');
    }

    public function getCheckInUser()
    {
        return $this->belongsToMany('App\User', 'properties_check_in', 'property_id', 'user_id')
            ->whereNull('properties_check_in.deleted_at');
    }

    public function getUnit()
    {
        return $this->hasMany('App\Units', 'property_id', 'id');
    }

    public function routeCheckpoint()
    {
        return $this->hasMany('App\Units', 'property_id', 'id')
            ->where('is_route', 1);
    }

    public function getActivity()
    {
        return $this->hasMany('App\Activitylogs', 'property_id', 'id');
    }

    public function getBuilding()
    {
        return $this->hasMany('App\Building');
    }

    public function buildingWithUnit()
    {
        return $this->hasMany('App\Building')
            ->with(
                [
                    'getUnit' => function ($query) {
                        $query->select('id', 'address1', 'address1', 'unit_number', 'property_id', 'building_id', 'barcode_id', 'is_active')
                            ->where('is_active', 1);
                    }
                ]
            );
    }

    public function getViolationByProperties()
    {
        return $this->customHasManyThrough('App\Violation', 'App\Units', 'property_id', 'barcode_id', 'id', 'barcode_id');
    }

    public function getNotesByProperties()
    {
        return $this->customHasManyThrough('App\BarcodeNotes', 'App\Units', 'property_id', 'barcode_id', 'id', 'barcode_id');
    }

    public function getBuildingIsActiveUnit()
    {
        return $this->hasMany('App\Building')
            ->with(
                [
                    'getUnit' => function ($query) {
                        $query->select('id', 'address1', 'address1', 'unit_number', 'property_id', 'building_id', 'barcode_id', 'is_active', 'is_route')
                            ->where('is_active', 1)
                            ->where('is_route', 0);
                    }
                ]
            )
            ->whereHas(
                'getUnit',
                function ($query) {
                    $query->where('is_active', 1)
                        ->where('is_route', 0);
                }
            );
    }

    public function todayHasProperty()
    {
        return $this->hasMany('App\PropertyFrequencies');
    }

    public function getAddressAttribute($value)
    {
        return ucwords($value);
    }

    public function getCityAttribute($value)
    {
        return ucwords($value);
    }

    public function getNameAttribute($value)
    {
        return ucwords($value);
    }

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)
                ->timezone('Asia/Kolkata');
    }

    public function walkThroughCompletedOrNot()
    {

        $startDate = \Carbon\Carbon::now()->format('Y-m-d') . " 06:00:00";
        $endDate = \Carbon\Carbon::now()->addDays(1)->format('Y-m-d') . " 05:59:59";

        return $this->hasMany('App\walkThroughRecord', 'property_id', 'id')
                ->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function checkWalkThrough()
    {

        return $this->hasMany('App\walkThroughRecord', 'property_id', 'id');
    }

    public function checkInSmsLog()
    {

        return $this->hasMany('App\CheckInSmsLog', 'property_id', 'id');
    }

    public function residents()
    {
        return $this->hasMany('App\Resident', 'property_id', 'id');
    }

    // public function hasTodayProperty()
    // {

    //     return $this->hasMany('App\property_frequencies', 'property_id', 'property_id');
    // }

    private function customHasManyThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null)
    {

        $through = new $through();

        /* Change the primary key :start */
        $through->primaryKey = 'barcode_id';
        /* Change the primary key :end */

        $firstKey = $firstKey ?: $this->getForeignKey();

        $secondKey = $secondKey ?: $through->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        $instance = $this->newRelatedInstance($related);

        return new HasManyThrough($instance->newQuery(), $this, $through, $firstKey, $secondKey, $localKey);
    }
}
