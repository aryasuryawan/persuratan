<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratAkses extends Model
{
    protected $table = 'surat_akses';

    protected $fillable = [
        'surat_id', 'user_id', 'level', 'diberikan_oleh_id',
    ];

    protected function casts(): array
    {
        return ['level' => 'string'];
    }

    public function surat(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function diberikanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diberikan_oleh_id');
    }
}