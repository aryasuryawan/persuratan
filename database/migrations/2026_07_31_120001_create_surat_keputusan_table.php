<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keputusan', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nomor_sk');
            $table->string('perihal');
            $table->string('kategori')->nullable();
            $table->date('tanggal_sk');
            $table->date('tanggal_berlaku')->nullable();
            $table->string('ditetapkan_oleh')->nullable();
            $table->text('ringkasan')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->foreignId('dicatat_oleh_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keputusan');
    }
};
