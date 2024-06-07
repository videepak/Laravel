<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskImage extends Model
{
    protected $fillable = [
        'files_name', 'task_id', 'activity_id'
    ];
}
