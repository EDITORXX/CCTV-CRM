<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportVideo extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'title', 'description', 'brand',
        'category', 'video_url', 'thumbnail', 'is_published', 'created_by',
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

    public function getYoutubeIdAttribute()
    {
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $this->video_url, $m)) {
            return $m[1];
        }
        return null;
    }

    public function getIsYoutubeAttribute()
    {
        return $this->youtube_id !== null;
    }

    public function getEmbedUrlAttribute()
    {
        if ($this->is_youtube) {
            return "https://www.youtube.com/embed/{$this->youtube_id}";
        }
        return $this->video_url;
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }
        if ($this->is_youtube) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/mqdefault.jpg";
        }
        return null;
    }
}
