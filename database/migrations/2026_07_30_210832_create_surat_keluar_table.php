<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nomor_surat')->unique();
            $table->string('tujuan_surat');
            $table->string('perihal');
            $table->date('tanggal_surat');
            $table->json('lampiran')->nullable();
            $table->foreignId('klasifikasi_id')->constrained('klasifikasi');
            $table->foreignId('surat_masuk_id')->nullable()->constrained('surat_masuk')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluar');
    }
};
