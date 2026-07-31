<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->morphs('kegiatan');
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->foreignId('diunggah_oleh_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_kegiatan');
    }
};
