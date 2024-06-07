<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id', 'admin_id', 'comment'
    ];

    
    public function ticket()
    {
        return $this->belongsTo(Tickets::class);
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::class, 'id', 'admin_id');
    }
}
