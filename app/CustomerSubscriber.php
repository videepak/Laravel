<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerSubscriber extends Model
{
    protected $fillable = [
        'user_id', 'customer_id', 'subscriber_id',
    ];
}
