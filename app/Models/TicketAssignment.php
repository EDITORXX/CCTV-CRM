<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAssignment extends Model
{
    protected $fillable = [
        'ticket_id', 'technician_id', 'assigned_by', 'assigned_at', 'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
