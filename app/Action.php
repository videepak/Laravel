<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends Model
{

    protected $table = 'action';

    use SoftDeletes;

    protected $fillable = [
        'company_id', 'action', 'type'
    ];

    public function getActionAttribute($value)
    {
        return ucwords($value);
    }

    public function removeAction()
    {
        return $this->hasOne(RemoveAction::class);
    }
}
