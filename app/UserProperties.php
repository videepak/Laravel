<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProperties extends Model
{

    protected $fillable = ['property_id', 'user_id', 'type'];
    protected $table = 'user_properties';

    use SoftDeletes;

    public function assignedusers()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function getPropertyDetail()
    {
        return $this->belongsTo('App\Property', 'property_id', 'id');
    }

    public function getUnitDetail()
    {
        return $this->hasMany('App\Units', 'property_id', 'property_id');
    }

    public function PropertyFrequencies() {
        return $this->hasMany('App\PropertyFrequencies', 'property_id', 'property_id');   
    }
}
