<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RiwayatStatus extends Model
{
    protected $table = 'riwayat_status';

    protected $fillable = [
        'uuid', 'surat_masuk_id', 'status_dari_id', 'status_ke_id',
        'dilakukan_oleh_id', 'atas_nama_pimpinan_id', 'catatan', 'disposisi_id',
    ];

    public function disposisi(): BelongsTo
    {
        return $this->belongsTo(Disposisi::class);
    }

    protected static function booted(): void
    {
        static::creating(function (RiwayatStatus $r) {
            if (empty($r->uuid)) {
                $r->uuid = (string) Str::uuid();
            }
        });
    }

    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class);
    }

    public function statusDari(): BelongsTo
    {
        return $this->belongsTo(StatusSurat::class, 'status_dari_id');
    }

    public function statusKe(): BelongsTo
    {
        return $this->belongsTo(StatusSurat::class, 'status_ke_id');
    }

    public function dilakukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh_id');
    }

    public function atasNamaPimpinan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atas_nama_pimpinan_id');
    }
}
