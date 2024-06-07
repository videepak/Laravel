<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrackViolation extends Model
{
    protected $fillable = [
        'violation_request', 'violation_response', 'user_id', 'type', 'url'
    ];
}
