<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RedundantRouteService extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['property_id', 'user_id'];
}