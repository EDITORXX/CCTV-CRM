<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LiveStream extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'token',
        'password',
        'title',
        'status',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
            'password'   => 'hashed',
        ];
    }

    public static function generateToken(): string
    {
        do {
            $token = Str::random(32);
        } while (static::withoutGlobalScopes()->where('token', $token)->exists());

        return $token;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function signals()
    {
        return $this->hasMany(LiveStreamSignal::class);
    }
}
