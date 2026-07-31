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

class KomitePersetujuan extends Model
{
    use PunyaAksesManual, PunyaFotoKegiatan, PunyaLampiran, PunyaRiwayatArsip;

    protected $table = 'komite_persetujuan';

    protected $fillable = [
        'uuid', 'judul', 'jenis', 'kategori', 'klasifikasi_id', 'tanggal_rapat', 'daftar_peserta',
        'nilai_pembelian', 'hasil', 'catatan_keputusan', 'is_rahasia',
        'dicatat_oleh_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_rapat' => 'date',
            'nilai_pembelian' => 'decimal:2',
            'is_rahasia' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (KomitePersetujuan $komite) {
            if (empty($komite->uuid)) {
                $komite->uuid = (string) Str::uuid();
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
            'judul' => 'Judul',
            'jenis' => 'Jenis',
            'tanggal_rapat' => 'Tanggal Rapat',
            'daftar_peserta' => 'Daftar Peserta',
            'nilai_pembelian' => 'Nilai Pembelian',
            'hasil' => 'Hasil',
            'catatan_keputusan' => 'Catatan Keputusan',
            'is_rahasia' => 'Sifat Arsip',
        ];
    }
}
