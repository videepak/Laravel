<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExcludedProperty extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'property_id', 
        'building_id', 'unit_id', 'exclude_date', 'report_issue_id'
    ];
}
