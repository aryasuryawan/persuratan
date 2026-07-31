<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposisi', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('surat_masuk_id')->constrained('surat_masuk')->cascadeOnDelete();
            $table->foreignId('dari_user_id')->constrained('users');
            $table->foreignId('kepada_user_id')->nullable()->constrained('users');
            $table->string('jenis', 30); // 'ke_pelaksana' or 'langsung_pimpinan'
            $table->foreignId('atas_nama_pimpinan_id')->nullable()->constrained('users');
            $table->text('catatan')->nullable();
            $table->text('catatan_pimpinan')->nullable();
            $table->date('tanggal_deadline')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisi');
    }
};
