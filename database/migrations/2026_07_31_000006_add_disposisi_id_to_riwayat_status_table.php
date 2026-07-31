<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_status', function (Blueprint $table) {
            $table->foreignId('disposisi_id')->nullable()->constrained('disposisi')->nullOnDelete()->after('atas_nama_pimpinan_id');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_status', function (Blueprint $table) {
            $table->dropForeign(['disposisi_id']);
            $table->dropColumn('disposisi_id');
        });
    }
};
