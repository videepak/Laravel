<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    public function stateinfo()
    {
        return $this->belongsTo('App\State', 'state');
    }
    
    public function getNameAttribute($val)
    {
        return ucwords($val);
    }
}
