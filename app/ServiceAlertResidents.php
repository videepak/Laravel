<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use illuminate\Database\Eloquent\SoftDeletes;

class ServiceAlertResidents extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fname', 'lname', 'mobile', 'email', 'property_id', 'building_id', 'unit_id'
    ];
}
