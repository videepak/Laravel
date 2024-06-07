<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportIssue extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'property_id',
        'building_id',
        'unit_id',
        'issue_date',
        'type',
        'user_id',
        'subscribers_id',
        'issue_reason_id'
    ];

    public function getUser()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function getProperty()
    {
        return $this->hasOne('App\Property', 'id', 'property_id');
    }

    public function getBuilding()
    {
        return $this->hasOne('App\Building', 'id', 'building_id');
    }

    public function getReportReason()
    {
        return $this->hasOne('App\IssueReason', 'id', 'issue_reason_id');
    }
}
