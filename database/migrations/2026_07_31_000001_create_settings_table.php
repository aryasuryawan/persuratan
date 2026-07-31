<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('label');
            $table->text('value')->nullable();
            $table->string('type', 20)->default('text');
            $table->timestamps();
        });

        DB::table('settings')->insert([
            ['key' => 'app_name', 'label' => 'Nama Aplikasi', 'value' => 'SiMPEL', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_logo', 'label' => 'Logo Aplikasi', 'value' => null, 'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_favicon', 'label' => 'Favicon', 'value' => null, 'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'lembaga_nama', 'label' => 'Nama Lembaga', 'value' => 'Yayasan Dompet Dhuafa Republika', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'lembaga_alamat', 'label' => 'Alamat Lembaga', 'value' => null, 'type' => 'textarea', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'lembaga_telepon', 'label' => 'No. Telepon', 'value' => null, 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'lembaga_email', 'label' => 'Email', 'value' => null, 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'lembaga_website', 'label' => 'Website', 'value' => null, 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_text', 'label' => 'Teks Footer', 'value' => 'SiMPEL v1.0.1 — Yayasan Dompet Dhuafa Republika', 'type' => 'textarea', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_copyright', 'label' => 'Copyright Footer', 'value' => null, 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_links', 'label' => 'Link Footer', 'value' => json_encode([['label' => 'Dokumentasi', 'url' => 'https://tabler.io/docs'], ['label' => 'Panduan', 'url' => 'https://tabler.io/docs']]), 'type' => 'json', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
