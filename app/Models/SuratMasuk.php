<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SuratMasuk extends Model
{
    use SoftDeletes;

    protected $table = 'surat_masuk';

    protected $fillable = [
        'uuid', 'nomor_agenda', 'nomor_surat', 'pengirim', 'perihal',
        'tanggal_surat', 'tanggal_diterima', 'lampiran', 'klasifikasi_id',
        'status_id', 'is_rahasia', 'lampiran_path', 'created_by',
        'verified_by', 'verified_at', 'completed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_diterima' => 'date',
            'lampiran' => 'array',
            'is_rahasia' => 'boolean',
            'verified_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SuratMasuk $s) {
            if (empty($s->uuid)) {
                $s->uuid = (string) Str::uuid();
            }
        });
    }

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(Klasifikasi::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusSurat::class, 'status_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function disposisi(): HasMany
    {
        return $this->hasMany(Disposisi::class);
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class);
    }
}
