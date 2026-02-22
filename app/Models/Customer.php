<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'phone', 'whatsapp', 'email', 'address', 'gstin', 'created_by',
    ];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }

    public function advances()
    {
        return $this->hasMany(CustomerAdvance::class);
    }

    /**
     * Total advance received minus total allocated to invoices (company-scoped via global scope).
     */
    public function getAdvanceBalance(): float
    {
        $totalAdvance = (float) $this->advances()->sum('amount');
        $totalAllocated = (float) CustomerAdvanceAllocation::whereHas('customerAdvance', function ($q) {
            $q->where('customer_id', $this->id);
        })->sum('amount');
        return $totalAdvance - $totalAllocated;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
