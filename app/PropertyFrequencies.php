<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyFrequencies extends Model
{

    protected $table = 'property_frequencies';
    use SoftDeletes;
    
    public function post()
    {
        return $this->hasMany('App\Property', 'property_id');
    }
}
