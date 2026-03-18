<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'customer_id', 'site_id', 'invoice_number', 'invoice_date',
        'remaining_due_date', 'due_reminder_sent_at',
        'is_gst', 'subtotal', 'gst_amount', 'discount', 'total', 'status', 'notes', 'created_by',
        'share_token', 'customer_signature', 'customer_ip', 'customer_signed_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'remaining_due_date' => 'date',
        'due_reminder_sent_at' => 'datetime',
        'customer_signed_at' => 'datetime',
        'is_gst' => 'boolean',
        'subtotal' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public static function generateShareToken(): string
    {
        do {
            $token = bin2hex(random_bytes(24));
        } while (static::where('share_token', $token)->exists());
        return $token;
    }

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

    public function invoiceExpenses()
    {
        return $this->hasMany(InvoiceExpense::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function getOutstandingAmountAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->payments()->sum('amount'));
    }
}
