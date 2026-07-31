<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notulensi_rapim', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('judul_rapat');
            $table->date('tanggal');
            $table->text('daftar_peserta')->nullable();
            $table->text('ringkasan_pembahasan');
            $table->text('keputusan_arahan')->nullable();
            $table->foreignId('dicatat_oleh_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notulensi_rapim');
    }
};
