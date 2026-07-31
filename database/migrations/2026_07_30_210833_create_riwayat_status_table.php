<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_status', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('surat_masuk_id')->constrained('surat_masuk')->cascadeOnDelete();
            $table->foreignId('status_dari_id')->nullable()->constrained('status_surat');
            $table->foreignId('status_ke_id')->constrained('status_surat');
            $table->foreignId('dilakukan_oleh_id')->constrained('users');
            $table->foreignId('atas_nama_pimpinan_id')->nullable()->constrained('users');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status');
    }
};
