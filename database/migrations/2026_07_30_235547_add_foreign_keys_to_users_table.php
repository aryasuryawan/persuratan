<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('direktorat_id')->nullable()->constrained()->nullOnDelete()->after('direktorat');
            $table->foreignId('divisi_id')->nullable()->constrained()->nullOnDelete()->after('direktorat_id');
            $table->foreignId('departemen_id')->nullable()->constrained()->nullOnDelete()->after('divisi_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['direktorat_id', 'divisi_id', 'departemen_id']);
            $table->dropColumn(['direktorat_id', 'divisi_id', 'departemen_id']);
        });
    }
};