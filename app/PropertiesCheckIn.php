<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertiesCheckIn extends Model
{

    use SoftDeletes;

    protected $table = "properties_check_in";
    protected $fillable = [
        'property_id', 'user_id', 'check_in', 'check_in_complete'
    ];
}
