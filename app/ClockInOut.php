<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClockInOut extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'clock_in', 'clock_out', 'reason',
    ];

    public function getUser()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
