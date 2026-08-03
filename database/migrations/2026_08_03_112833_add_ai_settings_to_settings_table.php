<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $settings = [
            ['ai_enabled', 'Aktifkan AI Assistant', '0', 'text'],
            ['xai_api_key', 'xAI API Key', null, 'text'],
            ['ai_base_url', 'AI Base URL', 'https://api.x.ai/v1', 'text'],
            ['ai_model', 'Model AI', 'grok-4.3', 'text'],
            ['ai_max_tokens', 'AI Max Tokens', '1024', 'text'],
            ['ai_timeout', 'AI Timeout (detik)', '30', 'text'],
            ['ai_rate_limit', 'AI Rate Limit (req/menit)', '10', 'text'],
        ];

        foreach ($settings as [$key, $label, $value, $type]) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['label' => $label, 'value' => $value, 'type' => $type, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'ai_enabled', 'xai_api_key', 'ai_base_url', 'ai_model',
            'ai_max_tokens', 'ai_timeout', 'ai_rate_limit',
        ])->delete();
    }
};
