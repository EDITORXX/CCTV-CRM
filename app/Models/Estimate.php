<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'customer_id', 'customer_name', 'customer_phone', 'site_id',
        'estimate_number', 'estimate_date', 'valid_until', 'is_gst', 'subtotal',
        'gst_amount', 'discount', 'total', 'status', 'notes',
        'converted_invoice_id', 'created_by',
    ];

    protected $casts = [
        'estimate_date' => 'date',
        'valid_until' => 'date',
        'is_gst' => 'boolean',
        'subtotal' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function items()
    {
        return $this->hasMany(EstimateItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function convertedInvoice()
    {
        return $this->belongsTo(Invoice::class, 'converted_invoice_id');
    }

    public function isConverted()
    {
        return $this->status === 'converted';
    }

    public function isWalkIn(): bool
    {
        return is_null($this->customer_id);
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        return $this->customer ? $this->customer->name : ($this->customer_name ?? 'Walk-in');
    }

    public function getCustomerDisplayPhoneAttribute(): ?string
    {
        return $this->customer ? $this->customer->phone : $this->customer_phone;
    }
}
