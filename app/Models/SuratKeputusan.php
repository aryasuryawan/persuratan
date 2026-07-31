<?php

namespace App\Models;

use App\Concerns\PunyaFotoKegiatan;
use App\Concerns\PunyaLampiran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SuratKeputusan extends Model
{
    use PunyaFotoKegiatan, PunyaLampiran;

    protected $table = 'surat_keputusan';

    protected $fillable = [
        'uuid', 'nomor_sk', 'perihal', 'kategori', 'tanggal_sk',
        'tanggal_berlaku', 'ditetapkan_oleh', 'ringkasan', 'status',
        'dicatat_oleh_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
            'tanggal_berlaku' => 'date',
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
}
