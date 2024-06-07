<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Units extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'address1', 'address2', 'unit_number', 'barcode_id', 'deleted_at',
        'building', 'building_id', 'property_id', 'is_route'
    ];

    public function service()
    {
        return $this->hasOne('App\Service', 'property_id', 'property_id');
    }

    public function getPropertyDetail()
    {
        return $this->belongsTo('App\Property', 'property_id');
    }

    public function getBuildingDetail()
    {
        return $this->belongsTo('App\Building', 'building_id');
    }

    public function getViolationByBarcode()
    {
        return $this->hasMany('App\Violation', 'barcode_id', 'barcode_id');
    }

    public function getSubmitViolationByBarcode()
    {
        return $this->hasMany('App\Violation', 'barcode_id', 'barcode_id')
                ->where('status', 2)->latest();
    }

    public function getActivityByBarcode()
    {
        return $this->hasMany('App\Activitylogs', 'barcode_id', 'barcode_id')
            ->latest();
    }

    public function pichUpOrNotPickUp()
    {
        $startDate = \Carbon\Carbon::now()->format('Y-m-d') . ' 06:00:00';
        $endDate = \Carbon\Carbon::now()->addDays(1)
                ->format('Y-m-d') . ' 05:59:59';

        return $this->hasOne('App\Activitylogs', 'barcode_id', 'barcode_id')
            ->whereBetween('updated_at', [$startDate, $endDate]);
    }

    public function getAddress1Attribute($value)
    {
        return ucwords($value);
    }

    public function getUnitNumberAttribute($value)
    {
        return ucwords(str_replace("\n", "", $value));
    }

    public function isRouteComplete()
    {
        return $this->hasMany('App\Activitylogs', 'barcode_id', 'barcode_id');
    }

    public function getResident()
    {
        return $this->hasOne('App\Resident', 'unit_id', 'id');
    }

    public function getResidentEmailHistory() 
    {
        return $this->belongsTo('App\ResidentEmailHistory' , 'unit_id');
    }
}
