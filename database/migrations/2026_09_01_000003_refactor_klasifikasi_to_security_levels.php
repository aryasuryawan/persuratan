<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrate topic-based klasifikasi records to topik table
        $topicKode = ['KEUANGAN', 'SDM', 'PROGRAM', 'PENGHIMPUNAN'];

        foreach ($topicKode as $kode) {
            $record = DB::table('klasifikasi')->where('kode', $kode)->first();
            if ($record) {
                DB::table('topik')->insert([
                    'uuid' => $record->uuid,
                    'kode' => $record->kode,
                    'nama' => $record->nama,
                    'deskripsi' => $record->deskripsi,
                    'color' => $record->color,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ]);
            }
        }

        // 2. Migrate existing document-topic relationships from klasifikasi_id
        // SuratMasuk
        $suratMasukTopics = DB::table('surat_masuk')
            ->whereIn('klasifikasi_id', function ($q) use ($topicKode) {
                $q->select('id')->from('klasifikasi')->whereIn('kode', $topicKode);
            })
            ->get();

        foreach ($suratMasukTopics as $row) {
            DB::table('document_topik')->insert([
                'document_type' => 'App\\Models\\SuratMasuk',
                'document_id' => $row->id,
                'topik_id' => $row->klasifikasi_id,
            ]);
        }

        // SuratKeluar
        $suratKeluarTopics = DB::table('surat_keluar')
            ->whereIn('klasifikasi_id', function ($q) use ($topicKode) {
                $q->select('id')->from('klasifikasi')->whereIn('kode', $topicKode);
            })
            ->get();

        foreach ($suratKeluarTopics as $row) {
            DB::table('document_topik')->insert([
                'document_type' => 'App\\Models\\SuratKeluar',
                'document_id' => $row->id,
                'topik_id' => $row->klasifikasi_id,
            ]);
        }

        // SuratKeputusan
        $skTopics = DB::table('surat_keputusan')
            ->whereIn('klasifikasi_id', function ($q) use ($topicKode) {
                $q->select('id')->from('klasifikasi')->whereIn('kode', $topicKode);
            })
            ->get();

        foreach ($skTopics as $row) {
            DB::table('document_topik')->insert([
                'document_type' => 'App\\Models\\SuratKeputusan',
                'document_id' => $row->id,
                'topik_id' => $row->klasifikasi_id,
            ]);
        }

        // 3. Delete topic-based records from klasifikasi table
        DB::table('klasifikasi')->whereIn('kode', $topicKode)->delete();

        // 4. Rename 'restricted' to 'TERBATAS'
        DB::table('klasifikasi')
            ->where('kode', 'restricted')
            ->update([
                'kode' => 'TERBATAS',
                'nama' => 'Restricted',
                'updated_at' => now(),
            ]);

        // 5. Reassign documents that had 'restricted' klasifikasi to new TERBATAS
        // (the record already exists after rename, so no FK issue)

        // 6. Drop is_rahasia column from document tables
        if (Schema::hasColumn('surat_masuk', 'is_rahasia')) {
            Schema::dropIfExists('surat_masuk_temp');
            Schema::table('surat_masuk', function ($table) {
                $table->dropColumn('is_rahasia');
            });
        }

        if (Schema::hasColumn('surat_keputusan', 'is_rahasia')) {
            Schema::table('surat_keputusan', function ($table) {
                $table->dropColumn('is_rahasia');
            });
        }

        if (Schema::hasColumn('komite_persetujuan', 'is_rahasia')) {
            Schema::table('komite_persetujuan', function ($table) {
                $table->dropColumn('is_rahasia');
            });
        }

        if (Schema::hasColumn('notulensi_rapim', 'is_rahasia')) {
            Schema::table('notulensi_rapim', function ($table) {
                $table->dropColumn('is_rahasia');
            });
        }
    }

    public function down(): void
    {
        // Reverse is complex - just log that rollback needs manual intervention
        \Illuminate\Support\Facades\Log::warning('Migration rollback for klasifikasi-security-refactor requires manual data restoration.');
    }
};
