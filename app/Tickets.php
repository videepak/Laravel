<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
 

class Tickets extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'subscriber_id', 'category_id', 'ticket_id', 'title', 'category_id', 'message', 'files_name', 'files_type', 'status', 'priority'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->hasMany(TicketComment::class, 'ticket_id', 'id');
    }

    public function ticketCategory()
    {
        return $this->hasOne(TicketCategory::class, 'id', 'category_id');
    }

}
