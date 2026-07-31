<?php

namespace App\Models;

use App\Concerns\PunyaFotoKegiatan;
use App\Concerns\PunyaLampiran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class KomitePersetujuan extends Model
{
    use PunyaFotoKegiatan, PunyaLampiran;

    protected $table = 'komite_persetujuan';

    protected $fillable = [
        'uuid', 'judul', 'jenis', 'tanggal_rapat', 'daftar_peserta',
        'nilai_pembelian', 'hasil', 'catatan_keputusan', 'dicatat_oleh_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_rapat' => 'date',
            'nilai_pembelian' => 'decimal:2',
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
}
