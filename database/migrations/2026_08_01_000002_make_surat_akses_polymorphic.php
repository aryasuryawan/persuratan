<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_akses', function (Blueprint $table) {
            $table->string('aksesable_type')->nullable()->after('id');
            $table->unsignedBigInteger('aksesable_id')->nullable()->after('aksesable_type');
            $table->index(['aksesable_type', 'aksesable_id']);
        });

        DB::table('surat_akses')->update([
            'aksesable_type' => \App\Models\SuratMasuk::class,
            'aksesable_id' => DB::raw('surat_id'),
        ]);

        Schema::table('surat_akses', function (Blueprint $table) {
            $table->dropForeign(['surat_id']);
            $table->dropColumn('surat_id');
            $table->unique(['aksesable_type', 'aksesable_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('surat_akses', function (Blueprint $table) {
            $table->dropUnique(['aksesable_type', 'aksesable_id', 'user_id']);
            $table->foreignId('surat_id')->nullable()->after('id')->constrained('surat_masuk')->cascadeOnDelete();
        });

        DB::table('surat_akses')
            ->where('aksesable_type', \App\Models\SuratMasuk::class)
            ->update(['surat_id' => DB::raw('aksesable_id')]);

        Schema::table('surat_akses', function (Blueprint $table) {
            $table->dropIndex(['aksesable_type', 'aksesable_id']);
            $table->dropColumn(['aksesable_type', 'aksesable_id']);
        });
    }
};
