<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'category', 'brand', 'model_number',
        'hsn_sac', 'unit', 'warranty_months', 'track_serial', 'sale_price', 'created_by',
    ];

    protected $casts = [
        'track_serial' => 'boolean',
        'sale_price' => 'decimal:2',
    ];

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
