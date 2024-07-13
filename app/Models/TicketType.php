<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'price', 'event_id'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function eventAttendees()
    {
        return $this->hasMany(EventAttendee::class, 'ticket_type_id');
    }
}
