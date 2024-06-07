<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RemoveAction extends Model
{
    protected $fillable = ['user_id', 'action_id', 'subscriber_id'];
}
