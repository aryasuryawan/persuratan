<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lampiran', function (Blueprint $table) {
            $table->id();
            $table->morphs('lampirkan');
            $table->string('nama_file');
            $table->string('path');
            $table->string('tipe_file', 10)->nullable();
            $table->unsignedBigInteger('ukuran_bytes')->nullable();
            $table->string('keterangan')->nullable();
            $table->foreignId('diunggah_oleh_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lampiran');
    }
};
