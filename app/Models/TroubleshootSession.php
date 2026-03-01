<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TroubleshootSession extends Model
{
    protected $fillable = [
        'company_id',
        'customer_id',
        'short_code',
        'password',
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

    public static function generateShortCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (static::where('short_code', $code)->exists());

        return $code;
    }

    public static function generatePin(): string
    {
        return (string) random_int(1000, 9999);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'waiting';
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'active']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function signals()
    {
        return $this->hasMany(TroubleshootSignal::class, 'troubleshoot_session_id');
    }
}
