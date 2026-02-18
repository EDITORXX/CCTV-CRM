<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'ticket_number', 'customer_id', 'site_id',
        'complaint_type', 'description', 'priority', 'status', 'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function assignments()
    {
        return $this->hasMany(TicketAssignment::class);
    }

    public function updates()
    {
        return $this->hasMany(TicketUpdate::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTechnicians()
    {
        return $this->belongsToMany(User::class, 'ticket_assignments', 'ticket_id', 'technician_id');
    }
}
