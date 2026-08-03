<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->index('tanggal_diterima', 'surat_masuk_tanggal_diterima_index');
            $table->index(['status_id', 'tanggal_diterima'], 'surat_masuk_status_tanggal_index');
        });

        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->index('tanggal_sk', 'surat_keputusan_tanggal_sk_index');
        });

        Schema::table('komite_persetujuan', function (Blueprint $table) {
            $table->index('tanggal_rapat', 'komite_persetujuan_tanggal_rapat_index');
        });

        Schema::table('notulensi_rapim', function (Blueprint $table) {
            $table->index('tanggal', 'notulensi_rapim_tanggal_index');
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->index('created_at', 'surat_keluar_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropIndex('surat_masuk_status_tanggal_index');
            $table->dropIndex('surat_masuk_tanggal_diterima_index');
        });

        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->dropIndex('surat_keputusan_tanggal_sk_index');
        });

        Schema::table('komite_persetujuan', function (Blueprint $table) {
            $table->dropIndex('komite_persetujuan_tanggal_rapat_index');
        });

        Schema::table('notulensi_rapim', function (Blueprint $table) {
            $table->dropIndex('notulensi_rapim_tanggal_index');
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropIndex('surat_keluar_created_at_index');
        });
    }
};
