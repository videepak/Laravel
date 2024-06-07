<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reason extends Model
{

    use SoftDeletes;

    protected $fillable = ['reason', 'user_id'];

    public function getReasonAttribute($value)
    {
        return ucfirst($value);
    }
}
