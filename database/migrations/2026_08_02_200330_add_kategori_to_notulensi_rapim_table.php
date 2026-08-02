<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notulensi_rapim', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('notulensi_rapim', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};