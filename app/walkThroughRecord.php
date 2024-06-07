<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class walkThroughRecord extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'property_id', 'building_id',
        'building_name', 'activity_id'
    ];
}
