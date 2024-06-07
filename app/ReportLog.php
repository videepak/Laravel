<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportLog extends Model
{
    protected $fillable = ['receiver', 'body'];
}
