<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatLog extends Model
{
    protected $table = 'ai_chat_logs';

    protected $fillable = [
        'user_id', 'question', 'model', 'modules_accessed', 'results_count',
        'success', 'prompt_tokens', 'completion_tokens', 'error_message', 'cost_estimate',
    ];

    protected function casts(): array
    {
        return [
            'modules_accessed' => 'array',
            'success' => 'boolean',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'results_count' => 'integer',
            'cost_estimate' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
