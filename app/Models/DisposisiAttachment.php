<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DisposisiAttachment extends Model
{
    protected $table = 'disposisi_attachments';

    protected $fillable = [
        'disposisi_id', 'original_name', 'stored_path', 'mime_type', 'file_size',
    ];

    public function disposisi(): BelongsTo
    {
        return $this->belongsTo(Disposisi::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->stored_path);
    }
}
