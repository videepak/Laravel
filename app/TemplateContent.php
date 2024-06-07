<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateContent extends Model
{
    use SoftDeletes;

    const TEMPLATEID = 1;
    const ISUSERSUBSCRIBER = 1;
    const ISUSERPROPERTYMANAGER = 1;
    
    protected $fillable = [
        'template_id',
        'subscriber_id',
        'content',
        'name',
        'subject',
        'user_id',
        'is_user'
    ];
    
    public function getContentAttribute($value)
    {
        return ucfirst($value);
    }
    
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }
    
    public function getSubjectAttribute($value)
    {
        return ucfirst($value);
    }
    
}
