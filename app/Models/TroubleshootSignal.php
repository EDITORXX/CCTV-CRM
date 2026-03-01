<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TroubleshootSignal extends Model
{
    protected $table = 'troubleshoot_signals';

    protected $fillable = [
        'troubleshoot_session_id',
        'from_peer',
        'to_peer',
        'type',
        'payload',
        'consumed',
    ];

    protected function casts(): array
    {
        return [
            'consumed' => 'boolean',
        ];
    }

    public function troubleshootSession()
    {
        return $this->belongsTo(TroubleshootSession::class);
    }
}
