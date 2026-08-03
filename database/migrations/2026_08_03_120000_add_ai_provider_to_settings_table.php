<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('settings')->updateOrInsert(
            ['key' => 'ai_provider'],
            ['label' => 'AI Provider', 'value' => 'xai', 'type' => 'text', 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('settings')
            ->where('key', 'ai_model')
            ->where('value', 'grok-4.1-fast')
            ->update(['value' => 'grok-4.3', 'updated_at' => $now]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'ai_provider')->delete();
    }
};
