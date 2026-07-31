<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Lampiran extends Model
{
    protected $table = 'lampiran';

    protected $fillable = [
        'lampirkan_type', 'lampirkan_id', 'nama_file', 'path', 'tipe_file',
        'ukuran_bytes', 'keterangan', 'diunggah_oleh_id',
    ];

    public function lampirkan(): MorphTo
    {
        return $this->morphTo();
    }

    public function diunggahOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh_id');
    }

    public function getUrlAttribute(): string
    {
        return route('arsip-media.lampiran.download', $this);
    }
}
