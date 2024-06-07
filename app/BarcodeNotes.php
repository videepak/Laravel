<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarcodeNotes extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'address1',
        'barcode_id',
        'address2',
        'unit',
        'long',
        'lat',
        'reason',
        'image',
        'user_id',
        'activityLogId',
        'description',
        'mobile_uniqe_id',
        'status',
        'notes_type'
    ];

    public function getReason()
    {
        return $this->belongsTo('App\Reason', 'reason');
    }

    public function getNoteSubject()
    {
        return $this->belongsTo('App\NoteSubject', 'reason');
    }

    public function getUnitNumber()
    {
        return $this->belongsTo('App\Units', 'barcode_id', 'barcode_id');
    }

    public function getPropertyName()
    {
        return $this->hasManyThrough('App\Property', 'barcode_id', 'barcode_id');
    }

    public function getUser()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function activity()
    {
        return $this->hasOne('App\Activitylogs', 'id');
    }
}
