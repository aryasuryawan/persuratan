<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->foreignId('pengirim_id')->nullable()->after('pengirim')->constrained('organisasi_pengirims')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pengirim_id');
        });
    }
};
