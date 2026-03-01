<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationTemplateItem extends Model
{
    protected $fillable = [
        'quotation_template_id', 'product_id', 'description', 'qty', 'unit_price', 'sort_order',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function quotationTemplate()
    {
        return $this->belongsTo(QuotationTemplate::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getDisplayNameAttribute()
    {
        if ($this->description) {
            return $this->description;
        }
        return $this->product ? $this->product->name : '—';
    }
}
