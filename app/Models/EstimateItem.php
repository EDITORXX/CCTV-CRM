<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateItem extends Model
{
    protected $fillable = [
        'estimate_id', 'product_id', 'qty', 'unit_price',
        'gst_percent', 'discount', 'total', 'warranty_months',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
