<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResidentEmailHistory extends Model
{
    use SoftDeletes;

    protected $table = 'resident_email_history';

    protected $fillable = [
        'id','unit_id','property_manager_id','property_id', 'resident_id','name', 'subject', 'cc', 'body'
    ];
    
    public function getUnitNumber()
    {
        return $this->hasOne('App\Units', 'id','unit_id');
    }

    public function getResident()
    {
        return $this->hasOne('App\Resident', 'resident_id', 'id');
    }
}
