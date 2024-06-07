<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Resident extends Model
{
    use SoftDeletes;
    use Notifiable;

    protected $fillable = [
        'id', 'firstname', 'lastname', 'mobile', 'email', 'unit_id', 'move_in_date', 'move_out_date', 'property_id', 'subscriber_id'
    ];

    public function setFirstnameAttribute($value)
    {
        $this->attributes['firstname'] = ucwords($value);
    }

    public function setLastnameAttribute($value)
    {
        $this->attributes['lastname'] = ucwords($value);
    }

    public function getUnit()
    {
        return $this->hasOne('App\Units', 'id', 'unit_id');
    }

    public function getViolation()
    {
        return $this->hasMany('App\ResidentsUnit', 'residents_id', 'id');
    }
}
