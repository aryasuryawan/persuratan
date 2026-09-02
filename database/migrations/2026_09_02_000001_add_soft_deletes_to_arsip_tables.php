<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('komite_persetujuan', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('notulensi_rapim', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('komite_persetujuan', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('notulensi_rapim', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
