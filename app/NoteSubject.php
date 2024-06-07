<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NoteSubject extends Model
{

    use SoftDeletes;

    protected $table = 'note_subjects';
    protected $fillable = [
        'subject', 'user_id', 'type'
    ];
}
