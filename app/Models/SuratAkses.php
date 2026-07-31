<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SuratAkses extends Model
{
    protected $table = 'surat_akses';

    protected $fillable = [
        'aksesable_type', 'aksesable_id', 'user_id', 'level',
        'diberikan_oleh_id',
    ];

    protected function casts(): array
    {
        return ['level' => 'string'];
    }

    public function aksesable(): MorphTo
    {
        return $this->morphTo();
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
