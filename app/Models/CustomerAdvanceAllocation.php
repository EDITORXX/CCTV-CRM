<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAdvanceAllocation extends Model
{
    protected $fillable = [
        'customer_advance_id', 'invoice_id', 'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function customerAdvance()
    {
        return $this->belongsTo(CustomerAdvance::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
