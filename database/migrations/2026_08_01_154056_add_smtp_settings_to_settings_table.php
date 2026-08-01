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
            ['smtp_enabled', 'Aktifkan Email Notifikasi', '0', 'text'],
            ['smtp_driver', 'Driver Email', 'smtp', 'text'],
            ['smtp_host', 'SMTP Host', 'smtp.gmail.com', 'text'],
            ['smtp_port', 'SMTP Port', '587', 'text'],
            ['smtp_username', 'SMTP Username', null, 'text'],
            ['smtp_password', 'SMTP Password', null, 'text'],
            ['smtp_encryption', 'SMTP Encryption', 'tls', 'text'],
            ['smtp_from_address', 'Dari Alamat (From)', null, 'text'],
            ['smtp_from_name', 'Dari Nama (From Name)', null, 'text'],
            ['mailersend_api_key', 'MailerSend API Key', null, 'text'],
            ['sendgrid_api_key', 'SendGrid API Key', null, 'text'],
            ['gmail_client_id', 'Gmail OAuth Client ID', null, 'text'],
            ['gmail_client_secret', 'Gmail OAuth Client Secret', null, 'text'],
            ['gmail_refresh_token', 'Gmail OAuth Refresh Token', null, 'text'],
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
            'smtp_enabled', 'smtp_driver', 'smtp_host', 'smtp_port', 'smtp_username',
            'smtp_password', 'smtp_encryption', 'smtp_from_address', 'smtp_from_name',
            'mailersend_api_key', 'sendgrid_api_key', 'gmail_client_id',
            'gmail_client_secret', 'gmail_refresh_token',
        ])->delete();
    }
};