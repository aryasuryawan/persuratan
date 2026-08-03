<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->foreignId('klasifikasi_id')->nullable()->constrained('klasifikasi')->nullOnDelete()->after('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->dropForeign(['klasifikasi_id']);
            $table->dropColumn('klasifikasi_id');
        });
    }
};