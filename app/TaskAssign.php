<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskAssign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id', 'task_id'
    ];
}
