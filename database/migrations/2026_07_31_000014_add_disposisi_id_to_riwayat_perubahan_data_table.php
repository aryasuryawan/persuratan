<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_perubahan_data', function (Blueprint $table) {
            $table->foreignId('disposisi_id')->nullable()->after('surat_masuk_id')->constrained('disposisi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_perubahan_data', function (Blueprint $table) {
            $table->dropForeign(['disposisi_id']);
            $table->dropColumn('disposisi_id');
        });
    }
};
