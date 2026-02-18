<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'invoice_id', 'amount', 'payment_date',
        'payment_method', 'reference_number', 'notes', 'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
