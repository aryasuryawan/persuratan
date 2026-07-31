<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Disposisi extends Model
{
    protected $table = 'disposisi';

    protected $fillable = [
        'uuid', 'surat_masuk_id', 'dari_user_id', 'kepada_user_id',
        'jenis', 'atas_nama_pimpinan_id', 'catatan', 'catatan_pimpinan',
        'tanggal_deadline', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_deadline' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Disposisi $d) {
            if (empty($d->uuid)) {
                $d->uuid = (string) Str::uuid();
            }
        });
    }

    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class);
    }

    public function dariUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    public function kepadaUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepada_user_id');
    }

    public function atasNamaPimpinan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atas_nama_pimpinan_id');
    }

    public function lampiran(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DisposisiAttachment::class);
    }

    public function followUps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DisposisiFollowUp::class);
    }
}
