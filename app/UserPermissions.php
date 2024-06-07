<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPermissions extends Model
{

    protected $table = 'user_permissions';
    protected $fillable = [
        'user_id', 
        'permission_id'
    ];

    public function permssion_name()
    {
        return $this->hasOne('App\Permission', 'id', 'permission_id');
    }
}
