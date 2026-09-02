<?php

namespace App\Models;

use App\Concerns\PunyaAksesManual;
use App\Concerns\PunyaFotoKegiatan;
use App\Concerns\PunyaLampiran;
use App\Concerns\PunyaRiwayatArsip;
use App\Models\Klasifikasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class NotulensiRapim extends Model
{
    use PunyaAksesManual, PunyaFotoKegiatan, PunyaLampiran, PunyaRiwayatArsip, SoftDeletes;

    protected $table = 'notulensi_rapim';

    protected $fillable = [
        'uuid', 'judul_rapat', 'tanggal', 'kategori', 'klasifikasi_id', 'daftar_peserta',
        'ringkasan_pembahasan', 'keputusan_arahan',
        'dicatat_oleh_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (NotulensiRapim $rapim) {
            if (empty($rapim->uuid)) {
                $rapim->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh_id');
    }

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(Klasifikasi::class);
    }

    public function topiks(): MorphToMany
    {
        return $this->morphToMany(Topik::class, 'document', 'document_topik');
    }

    public function getIsRahasiaAttribute(): bool
    {
        return in_array($this->klasifikasi?->kode, ['TERBATAS', 'RAHASIA']);
    }

    protected function labelsRiwayat(): array
    {
        return [
            'judul_rapat' => 'Judul Rapat',
            'tanggal' => 'Tanggal',
            'daftar_peserta' => 'Daftar Peserta',
            'ringkasan_pembahasan' => 'Ringkasan Pembahasan',
            'keputusan_arahan' => 'Keputusan / Arahan',
            'is_rahasia' => 'Sifat Arsip',
        ];
    }
}
