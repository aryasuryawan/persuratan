<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RiwayatArsip extends Model
{
    protected $table = 'riwayat_arsip';

    protected $fillable = [
        'arsipable_type', 'arsipable_id', 'field', 'label',
        'nilai_lama', 'nilai_baru', 'dilakukan_oleh_id', 'catatan',
    ];

    public function arsipable(): MorphTo
    {
        return $this->morphTo();
    }

    public function dilakukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh_id');
    }
}
