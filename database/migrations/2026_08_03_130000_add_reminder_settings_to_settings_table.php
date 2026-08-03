<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $settings = [
            ['reminder_disposisi_enabled', 'Reminder Deadline Disposisi', '0', 'text'],
            ['reminder_disposisi_hour', 'Jam Reminder Disposisi', '8', 'text'],
            ['reminder_disposisi_message', 'Pesan Reminder Disposisi', "Disposisi berikut melewati deadline tetapi laporan follow-up belum diisi. Mohon segera melengkapi.", 'text'],
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
            'reminder_disposisi_enabled', 'reminder_disposisi_hour',
        ])->delete();
    }
};
