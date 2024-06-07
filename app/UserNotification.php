<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id', 'subscriber_id', 'type', 'email', 'sms', 'day_frequency',
    ];
}
