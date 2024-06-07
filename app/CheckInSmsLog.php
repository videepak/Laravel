<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckInSmsLog extends Model
{

    protected $fillable = [
      'message',
      'receiver_id', 'sender_id', 'property_id'
    ];
}
