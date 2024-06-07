<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscriber extends Model
{
    
    use SoftDeletes;

    protected $fillable = [
        'service_in_time', 'service_out_time', 'resident_alert'
      ];

    const DISABLEAlERT = 0;
    const ENABLEAlERT = 1;
    
    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function subscription()
    {
        return $this->hasOne('App\Subscriptions', 'id', 'subscription_id');
    }

    public function employees()
    {
        return $this->hasMany('App\User', 'subscriber_id', 'id');
    }
    
    public function getNotification()
    {
        return $this->hasMany('App\UserNotification', 'subscriber_id', 'id');
    }

    public function getState()
    {
        return $this->hasOne('App\State', 'id', 'state');
    }

    public function getProperties()
    {
        return $this->hasMany('App\Property', 'subscriber_id', 'id');
    }
}
