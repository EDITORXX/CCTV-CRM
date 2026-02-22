<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAdvance extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'customer_id', 'amount', 'receipt_number', 'payment_date',
        'payment_method', 'reference_number', 'notes', 'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function allocations()
    {
        return $this->hasMany(CustomerAdvanceAllocation::class, 'customer_advance_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Total amount already allocated from this advance to invoices */
    public function allocatedAmount(): float
    {
        return (float) $this->allocations()->sum('amount');
    }

    /** Remaining balance of this advance that can still be applied */
    public function getRemainingBalanceAttribute(): float
    {
        return (float) $this->amount - $this->allocatedAmount();
    }
}
