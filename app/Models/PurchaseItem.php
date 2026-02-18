<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id', 'product_id', 'qty', 'unit_price', 'gst_percent', 'total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class);
    }
}
