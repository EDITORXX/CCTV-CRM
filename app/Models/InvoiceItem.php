<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'product_id', 'qty', 'unit_price',
        'gst_percent', 'discount', 'total', 'warranty_months',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class, 'invoice_item_id');
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }
}
