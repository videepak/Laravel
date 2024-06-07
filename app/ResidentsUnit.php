<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResidentsUnit extends Model
{
    use SoftDeletes;

    protected $table = 'residents_unit';

    protected $fillable = [
        'residents_id',
        'unit_id',
        'violation_id',
        'move_in_date',
        'move_out_date'
    ];

    public function getResident()
    {
        return $this->hasOne('App\Resident', 'id','residents_id');
    }
    
    public function getUnitNumber()
    {
        return $this->hasOne('App\Units', 'id','unit_id');
    }

}
