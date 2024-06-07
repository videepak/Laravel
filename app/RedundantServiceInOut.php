<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class RedundantServiceInOut extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['property_id', 'user_id', 'service_in', 'service_out', 'subscriber_id'];
}
