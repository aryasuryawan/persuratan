<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komite_persetujuan', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('judul');
            $table->string('jenis', 20);
            $table->date('tanggal_rapat');
            $table->text('daftar_peserta')->nullable();
            $table->decimal('nilai_pembelian', 18, 2)->nullable();
            $table->string('hasil', 20);
            $table->text('catatan_keputusan')->nullable();
            $table->foreignId('dicatat_oleh_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komite_persetujuan');
    }
};
