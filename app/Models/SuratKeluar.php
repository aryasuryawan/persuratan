<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SuratKeluar extends Model
{
    use SoftDeletes;
    protected $table = 'surat_keluar';

    protected $fillable = [
        'uuid', 'nomor_surat', 'tujuan_surat', 'perihal', 'tanggal_surat',
        'lampiran', 'klasifikasi_id', 'pimpinan_pengirim_id', 'kode_surat_id', 'surat_masuk_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'lampiran' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SuratKeluar $s) {
            if (empty($s->uuid)) {
                $s->uuid = (string) Str::uuid();
            }
        });
    }

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(Klasifikasi::class);
    }

    public function pimpinanPengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pimpinan_pengirim_id');
    }

    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class);
    }

    public function kodeSurat(): BelongsTo
    {
        return $this->belongsTo(KodeSurat::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}