<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_arsip', function (Blueprint $table) {
            $table->id();
            $table->string('arsipable_type');
            $table->unsignedBigInteger('arsipable_id');
            $table->string('field');
            $table->string('label')->nullable();
            $table->text('nilai_lama')->nullable();
            $table->text('nilai_baru')->nullable();
            $table->foreignId('dilakukan_oleh_id')->constrained('users');
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->index(['arsipable_type', 'arsipable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_arsip');
    }
};
