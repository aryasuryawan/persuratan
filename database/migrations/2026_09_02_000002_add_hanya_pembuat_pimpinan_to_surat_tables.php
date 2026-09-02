<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->boolean('hanya_pembuat_pimpinan')->default(false);
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->boolean('hanya_pembuat_pimpinan')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropColumn('hanya_pembuat_pimpinan');
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropColumn('hanya_pembuat_pimpinan');
        });
    }
};