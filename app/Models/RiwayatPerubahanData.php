<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RiwayatPerubahanData extends Model
{
    protected $table = 'riwayat_perubahan_data';

    protected $fillable = [
        'uuid', 'surat_masuk_id', 'disposisi_id', 'dilakukan_oleh_id', 'perubahan', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'perubahan' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RiwayatPerubahanData $r) {
            if (empty($r->uuid)) {
                $r->uuid = (string) Str::uuid();
            }
        });
    }

    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class);
    }

    public function dilakukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh_id');
    }

    public function disposisi(): BelongsTo
    {
        return $this->belongsTo(Disposisi::class);
    }
}
