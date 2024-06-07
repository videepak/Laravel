<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IssueReason extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'reason', 'user_id'
    ];
}
