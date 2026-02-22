<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportArticle extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'title', 'content', 'type', 'brand',
        'category', 'is_published', 'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFaqs($query)
    {
        return $query->where('type', 'faq');
    }

    public function scopeGuides($query)
    {
        return $query->where('type', 'guide');
    }
}
