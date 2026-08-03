<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->foreignId('pimpinan_pengirim_id')->nullable()->constrained('users')->after('klasifikasi_id');
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropForeign(['pimpinan_pengirim_id']);
            $table->dropColumn('pimpinan_pengirim_id');
        });
    }
};