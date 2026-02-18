<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'customer_id', 'site_id', 'invoice_number', 'invoice_date',
        'is_gst', 'subtotal', 'gst_amount', 'discount', 'total', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
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
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }
}
