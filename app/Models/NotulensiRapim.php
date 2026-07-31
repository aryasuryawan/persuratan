<?php

namespace App\Models;

use App\Concerns\PunyaFotoKegiatan;
use App\Concerns\PunyaLampiran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NotulensiRapim extends Model
{
    use PunyaFotoKegiatan, PunyaLampiran;

    protected $table = 'notulensi_rapim';

    protected $fillable = [
        'uuid', 'judul_rapat', 'tanggal', 'daftar_peserta',
        'ringkasan_pembahasan', 'keputusan_arahan', 'dicatat_oleh_id',
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
}
