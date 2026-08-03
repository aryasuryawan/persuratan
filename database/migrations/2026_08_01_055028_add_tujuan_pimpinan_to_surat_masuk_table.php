<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->foreignId('tujuan_pimpinan_id')->nullable()->constrained('users')->after('klasifikasi_id');
        });
    }

    public function down(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropForeign(['tujuan_pimpinan_id']);
            $table->dropColumn('tujuan_pimpinan_id');
        });
    }
};