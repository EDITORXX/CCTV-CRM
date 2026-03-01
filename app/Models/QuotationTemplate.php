<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuotationTemplate extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'slug'];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function items()
    {
        return $this->hasMany(QuotationTemplateItem::class, 'quotation_template_id')->orderBy('sort_order');
    }

    public function getTotalAttribute()
    {
        return $this->items->sum(fn ($i) => $i->qty * $i->unit_price);
    }
}
