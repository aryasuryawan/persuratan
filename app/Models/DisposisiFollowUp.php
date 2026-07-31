<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisposisiFollowUp extends Model
{
    protected $table = 'disposisi_follow_ups';

    protected $fillable = [
        'disposisi_id', 'user_id', 'keterangan', 'lampiran',
    ];

    protected function casts(): array
    {
        return [
            'lampiran' => 'array',
        ];
    }

    public function disposisi(): BelongsTo
    {
        return $this->belongsTo(Disposisi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
