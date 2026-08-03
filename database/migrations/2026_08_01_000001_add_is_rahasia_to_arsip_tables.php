<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->boolean('is_rahasia')->default(false)->after('status');
        });

        Schema::table('komite_persetujuan', function (Blueprint $table) {
            $table->boolean('is_rahasia')->default(false)->after('hasil');
        });

        Schema::table('notulensi_rapim', function (Blueprint $table) {
            $table->boolean('is_rahasia')->default(false)->after('judul_rapat');
        });
    }

    public function down(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->dropColumn('is_rahasia');
        });

        Schema::table('komite_persetujuan', function (Blueprint $table) {
            $table->dropColumn('is_rahasia');
        });

        Schema::table('notulensi_rapim', function (Blueprint $table) {
            $table->dropColumn('is_rahasia');
        });
    }
};
