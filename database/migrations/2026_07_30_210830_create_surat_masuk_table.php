<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nomor_agenda', 50)->unique();
            $table->string('nomor_surat')->nullable();
            $table->string('pengirim');
            $table->string('perihal');
            $table->date('tanggal_surat')->nullable();
            $table->date('tanggal_diterima');
            $table->json('lampiran')->nullable();
            $table->foreignId('klasifikasi_id')->constrained('klasifikasi');
            $table->foreignId('status_id')->constrained('status_surat');
            $table->boolean('is_rahasia')->default(false);
            $table->string('lampiran_path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_masuk');
    }
};
