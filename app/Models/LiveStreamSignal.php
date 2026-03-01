<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveStreamSignal extends Model
{
    protected $fillable = [
        'live_stream_id',
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

    public function liveStream()
    {
        return $this->belongsTo(LiveStream::class);
    }
}
