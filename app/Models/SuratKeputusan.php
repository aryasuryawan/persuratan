<?php

namespace App\Models;

use App\Concerns\PunyaAksesManual;
use App\Concerns\PunyaFotoKegiatan;
use App\Concerns\PunyaLampiran;
use App\Concerns\PunyaRiwayatArsip;
use App\Models\Klasifikasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SuratKeputusan extends Model
{
    use PunyaAksesManual, PunyaFotoKegiatan, PunyaLampiran, PunyaRiwayatArsip;

    protected $table = 'surat_keputusan';

    protected $fillable = [
        'uuid', 'nomor_sk', 'perihal', 'kategori', 'klasifikasi_id', 'tanggal_sk',
        'tanggal_berlaku', 'ditetapkan_oleh', 'ringkasan', 'status',
        'is_rahasia', 'dicatat_oleh_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
            'tanggal_berlaku' => 'date',
            'is_rahasia' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SuratKeputusan $sk) {
            if (empty($sk->uuid)) {
                $sk->uuid = (string) Str::uuid();
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

    protected function labelsRiwayat(): array
    {
        return [
            'nomor_sk' => 'No. SK',
            'perihal' => 'Perihal',
            'kategori' => 'Kategori',
            'tanggal_sk' => 'Tanggal SK',
            'tanggal_berlaku' => 'Tanggal Berlaku',
            'ditetapkan_oleh' => 'Ditetapkan Oleh',
            'ringkasan' => 'Ringkasan',
            'status' => 'Status',
            'is_rahasia' => 'Sifat Surat',
        ];
    }
}
