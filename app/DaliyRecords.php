<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DaliyRecords extends Model
{
    protected $fillable = [
        'property_id', 'pickup_completed', 'active_units', 'route_checkpoints_scanned', 'checkpoints_by_property', 'building_walk_throughs', 'active_building', 'first_checkin', 'last_checkout', 'total_tasks_completed', 'number_of_missed_property_checkouts', 'total_task', 'record_date'
    ];

    public function property()
    {
        return $this->hasOne('\App\Property', 'id', 'property_id');
    }
}
