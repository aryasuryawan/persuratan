<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FotoKegiatan extends Model
{
    protected $table = 'foto_kegiatan';

    protected $fillable = [
        'kegiatan_type', 'kegiatan_id', 'path', 'caption', 'urutan',
        'diunggah_oleh_id',
    ];

    public function kegiatan(): MorphTo
    {
        return $this->morphTo();
    }

    public function diunggahOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh_id');
    }

    public function getUrlAttribute(): string
    {
        return route('arsip-media.foto.stream', $this);
    }
}
