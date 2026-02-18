<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'invoice_item_id', 'serial_number_id', 'product_id',
        'customer_id', 'start_date', 'end_date', 'status', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function serialNumber()
    {
        return $this->belongsTo(SerialNumber::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }
}
