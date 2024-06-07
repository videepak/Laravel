<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViolationImages extends Model
{

    protected $table = 'violation_images';
    protected $fillable = [
        'filename', 'violation_id'
    ];
}
