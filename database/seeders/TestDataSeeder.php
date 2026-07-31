<?php

namespace Database\Seeders;

use App\Models\Disposisi;
use App\Models\Klasifikasi;
use App\Models\RiwayatStatus;
use App\Models\StatusSurat;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@dd.or.id')->first();
        $sekretaris = User::where('email', 'sekretaris@dd.or.id')->first();
        $pimpinan = User::where('email', 'pimpinan@dd.or.id')->first();

        // Ambil user uji coba yang dipertahankan (PreservedDataSeeder)
        $users = [];
        $userEmails = [
            'ahmad@dd.or.id', 'siti@dd.or.id', 'bambang@dd.or.id', 'rina@dd.or.id',
            'dedi@dd.or.id', 'fitri@dd.or.id', 'hendra@dd.or.id', 'dewi@dd.or.id',
        ];
        foreach ($userEmails as $email) {
            $u = User::where('email', $email)->first();
            if ($u) {
                $users[$email] = $u;
            }
        }

        // Relasi sekretaris-pimpinan
        \App\Models\SekretarisPimpinan::create([
            'sekretaris_id' => $sekretaris->id,
            'pimpinan_id' => $pimpinan->id,
            'is_active' => true,
        ]);
        \App\Models\SekretarisPimpinan::create([
            'sekretaris_id' => $users['dewi@dd.or.id']->id,
            'pimpinan_id' => $pimpinan->id,
            'is_active' => true,
        ]);

        $klasifikasi = Klasifikasi::pluck('id', 'kode');
        $status = StatusSurat::pluck('id', 'kode');

        $surats = [];

        // Surat 1: selesai (disposisi ke Direktorat Program)
        $s = SuratMasuk::create([
            'nomor_agenda' => 'AG-20260731-0001',
            'nomor_surat' => '001/DDF/VI/2026',
            'pengirim' => 'Kementerian Sosial RI',
            'perihal' => 'Undangan Rapat Koordinasi Program Sosial',
            'tanggal_surat' => '2026-07-28',
            'tanggal_diterima' => '2026-07-29',
            'klasifikasi_id' => $klasifikasi['UMUM'],
            'status_id' => $status['completed'],
            'created_by' => $sekretaris->id,
            'verified_by' => $sekretaris->id,
            'verified_at' => now()->subDays(2),
            'completed_at' => now()->subDay(),
            'notes' => 'Surat undangan rapat koordinasi tingkat nasional.',
        ]);
        $surats[] = $s;

        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => null, 'status_ke_id' => $status['received'], 'dilakukan_oleh_id' => $sekretaris->id, 'catatan' => 'Surat diterima']);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['received'], 'status_ke_id' => $status['verified'], 'dilakukan_oleh_id' => $sekretaris->id, 'catatan' => 'Diverifikasi']);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['verified'], 'status_ke_id' => $status['forwarded_to_leadership'], 'dilakukan_oleh_id' => $sekretaris->id, 'atas_nama_pimpinan_id' => $pimpinan->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['forwarded_to_leadership'], 'status_ke_id' => $status['assigned'], 'dilakukan_oleh_id' => $pimpinan->id, 'catatan' => 'Disposisi ke Direktorat Program']);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['assigned'], 'status_ke_id' => $status['completed'], 'dilakukan_oleh_id' => $users['ahmad@dd.or.id']->id, 'catatan' => 'Selesai diproses oleh Direktorat Program']);

        Disposisi::create([
            'surat_masuk_id' => $s->id,
            'dari_user_id' => $sekretaris->id,
            'kepada_user_id' => $users['ahmad@dd.or.id']->id,
            'jenis' => 'ke_pelaksana',
            'atas_nama_pimpinan_id' => $pimpinan->id,
            'catatan' => 'Mohon ditindaklanjuti undangan rapat koordinasi.',
            'tanggal_deadline' => '2026-08-05',
        ]);

        // Surat 2: perlu verifikasi (dari CC Security)
        $s = SuratMasuk::create([
            'nomor_agenda' => 'AG-20260731-0002',
            'nomor_surat' => '045/KEU/VII/2026',
            'pengirim' => 'Bank Syariah Indonesia',
            'perihal' => 'Konfirmasi Saldo Rekening Yayasan',
            'tanggal_surat' => '2026-07-30',
            'tanggal_diterima' => '2026-07-31',
            'klasifikasi_id' => $klasifikasi['KEUANGAN'],
            'status_id' => $status['received'],
            'is_rahasia' => true,
            'created_by' => $users['hendra@dd.or.id']->id,
            'notes' => 'Dilampirkan mutasi rekening.',
        ]);
        $surats[] = $s;

        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => null, 'status_ke_id' => $status['received'], 'dilakukan_oleh_id' => $users['hendra@dd.or.id']->id]);

        // Surat 3: sedang diproses (disposisi ke Direktorat SDM)
        $s = SuratMasuk::create([
            'nomor_agenda' => 'AG-20260731-0003',
            'nomor_surat' => '078/SDM/VII/2026',
            'pengirim' => 'Universitas Indonesia',
            'perihal' => 'Permohonan Magang Mahasiswa',
            'tanggal_surat' => '2026-07-25',
            'tanggal_diterima' => '2026-07-28',
            'klasifikasi_id' => $klasifikasi['SDM'],
            'status_id' => $status['in_progress'],
            'is_rahasia' => true,
            'created_by' => $sekretaris->id,
            'verified_by' => $sekretaris->id,
            'verified_at' => now()->subDays(3),
        ]);
        $surats[] = $s;

        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => null, 'status_ke_id' => $status['received'], 'dilakukan_oleh_id' => $sekretaris->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['received'], 'status_ke_id' => $status['verified'], 'dilakukan_oleh_id' => $sekretaris->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['verified'], 'status_ke_id' => $status['assigned'], 'dilakukan_oleh_id' => $sekretaris->id, 'atas_nama_pimpinan_id' => $pimpinan->id, 'catatan' => 'Disposisi ke Direktorat SDM']);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['assigned'], 'status_ke_id' => $status['in_progress'], 'dilakukan_oleh_id' => $users['bambang@dd.or.id']->id, 'catatan' => 'Sedang direview oleh SDM']);

        Disposisi::create([
            'surat_masuk_id' => $s->id,
            'dari_user_id' => $sekretaris->id,
            'kepada_user_id' => $users['bambang@dd.or.id']->id,
            'jenis' => 'ke_pelaksana',
            'atas_nama_pimpinan_id' => $pimpinan->id,
            'catatan' => 'Tolong review permohonan magang.',
            'tanggal_deadline' => '2026-08-10',
        ]);

        // Surat 4: disposisi langsung pimpinan
        $s = SuratMasuk::create([
            'nomor_agenda' => 'AG-20260731-0004',
            'nomor_surat' => '112/PROG/VII/2026',
            'pengirim' => 'Kementerian Agama RI',
            'perihal' => 'Tawaran Kerjasama Program Pemberdayaan',
            'tanggal_surat' => '2026-07-27',
            'tanggal_diterima' => '2026-07-29',
            'klasifikasi_id' => $klasifikasi['PROGRAM'],
            'status_id' => $status['handled_by_leadership'],
            'created_by' => $sekretaris->id,
            'verified_by' => $sekretaris->id,
            'verified_at' => now()->subDays(2),
        ]);
        $surats[] = $s;

        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => null, 'status_ke_id' => $status['received'], 'dilakukan_oleh_id' => $sekretaris->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['received'], 'status_ke_id' => $status['verified'], 'dilakukan_oleh_id' => $sekretaris->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['verified'], 'status_ke_id' => $status['handled_by_leadership'], 'dilakukan_oleh_id' => $sekretaris->id, 'atas_nama_pimpinan_id' => $pimpinan->id]);

        Disposisi::create([
            'surat_masuk_id' => $s->id,
            'dari_user_id' => $sekretaris->id,
            'kepada_user_id' => null,
            'jenis' => 'langsung_pimpinan',
            'atas_nama_pimpinan_id' => $pimpinan->id,
            'catatan_pimpinan' => 'Akan ditinjau lebih lanjut, koordinasikan dengan Direktorat Program.',
        ]);

        // Surat 5: surat rahasia (dibuat oleh sekretaris 2, disposisi ke Direktorat Keuangan)
        $s = SuratMasuk::create([
            'nomor_agenda' => 'AG-20260731-0005',
            'nomor_surat' => 'RHS-001/VII/2026',
            'pengirim' => 'Divisi Internal Audit',
            'perihal' => 'Laporan Audit Keuangan Q2 2026',
            'tanggal_surat' => '2026-07-28',
            'tanggal_diterima' => '2026-07-30',
            'klasifikasi_id' => $klasifikasi['RAHASIA'],
            'status_id' => $status['forwarded_to_leadership'],
            'is_rahasia' => true,
            'created_by' => $users['dewi@dd.or.id']->id,
            'verified_by' => $users['dewi@dd.or.id']->id,
            'verified_at' => now()->subDay(),
        ]);
        $surats[] = $s;

        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => null, 'status_ke_id' => $status['received'], 'dilakukan_oleh_id' => $users['dewi@dd.or.id']->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['received'], 'status_ke_id' => $status['verified'], 'dilakukan_oleh_id' => $users['dewi@dd.or.id']->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['verified'], 'status_ke_id' => $status['forwarded_to_leadership'], 'dilakukan_oleh_id' => $users['dewi@dd.or.id']->id, 'atas_nama_pimpinan_id' => $pimpinan->id, 'catatan' => 'Laporan audit untuk pimpinan']);

        // Surat 6: disposisi ke Direktorat Fundraising (selesai)
        $s = SuratMasuk::create([
            'nomor_agenda' => 'AG-20260731-0006',
            'nomor_surat' => '200/FUND/VII/2026',
            'pengirim' => 'Global Charity Foundation',
            'perihal' => 'Proposal Kerjasama Program Beasiswa',
            'tanggal_surat' => '2026-07-26',
            'tanggal_diterima' => '2026-07-27',
            'klasifikasi_id' => $klasifikasi['PROGRAM'],
            'status_id' => $status['completed'],
            'created_by' => $sekretaris->id,
            'verified_by' => $sekretaris->id,
            'verified_at' => now()->subDays(4),
            'completed_at' => now()->subDays(2),
        ]);
        $surats[] = $s;

        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => null, 'status_ke_id' => $status['received'], 'dilakukan_oleh_id' => $sekretaris->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['received'], 'status_ke_id' => $status['verified'], 'dilakukan_oleh_id' => $sekretaris->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['verified'], 'status_ke_id' => $status['forwarded_to_leadership'], 'dilakukan_oleh_id' => $sekretaris->id, 'atas_nama_pimpinan_id' => $pimpinan->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['forwarded_to_leadership'], 'status_ke_id' => $status['assigned'], 'dilakukan_oleh_id' => $pimpinan->id, 'catatan' => 'Disposisi ke Fundraising']);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['assigned'], 'status_ke_id' => $status['completed'], 'dilakukan_oleh_id' => $users['rina@dd.or.id']->id, 'catatan' => 'Proposal diterima, beasiswa dikonfirmasi']);

        Disposisi::create([
            'surat_masuk_id' => $s->id,
            'dari_user_id' => $sekretaris->id,
            'kepada_user_id' => $users['rina@dd.or.id']->id,
            'jenis' => 'ke_pelaksana',
            'atas_nama_pimpinan_id' => $pimpinan->id,
            'catatan' => 'Review proposal kerjasama beasiswa.',
            'tanggal_deadline' => '2026-08-07',
        ]);

        // Surat 7: disposisi ke Direktorat IT
        $s = SuratMasuk::create([
            'nomor_agenda' => 'AG-20260731-0007',
            'nomor_surat' => '045/IT/VII/2026',
            'pengirim' => 'PT Teknologi Nusantara',
            'perihal' => 'Penawaran Sistem ERP Terintegrasi',
            'tanggal_surat' => '2026-07-29',
            'tanggal_diterima' => '2026-07-31',
            'klasifikasi_id' => $klasifikasi['UMUM'],
            'status_id' => $status['assigned'],
            'created_by' => $sekretaris->id,
            'verified_by' => $sekretaris->id,
            'verified_at' => now(),
        ]);
        $surats[] = $s;

        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => null, 'status_ke_id' => $status['received'], 'dilakukan_oleh_id' => $sekretaris->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['received'], 'status_ke_id' => $status['verified'], 'dilakukan_oleh_id' => $sekretaris->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['verified'], 'status_ke_id' => $status['forwarded_to_leadership'], 'dilakukan_oleh_id' => $sekretaris->id, 'atas_nama_pimpinan_id' => $pimpinan->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['forwarded_to_leadership'], 'status_ke_id' => $status['assigned'], 'dilakukan_oleh_id' => $pimpinan->id, 'catatan' => 'Disposisi ke IT untuk evaluasi']);

        Disposisi::create([
            'surat_masuk_id' => $s->id,
            'dari_user_id' => $sekretaris->id,
            'kepada_user_id' => $users['dedi@dd.or.id']->id,
            'jenis' => 'ke_pelaksana',
            'atas_nama_pimpinan_id' => $pimpinan->id,
            'catatan' => 'Evaluasi penawaran ERP, siapkan rekomendasi.',
            'tanggal_deadline' => '2026-08-15',
        ]);

        // Surat 8: diarsipkan
        $s = SuratMasuk::create([
            'nomor_agenda' => 'AG-20260725-0008',
            'nomor_surat' => '033/UMUM/VII/2026',
            'pengirim' => 'PT Pos Indonesia',
            'perihal' => 'Penawaran Jasa Pengiriman Dokumen',
            'tanggal_surat' => '2026-07-22',
            'tanggal_diterima' => '2026-07-23',
            'klasifikasi_id' => $klasifikasi['UMUM'],
            'status_id' => $status['archived'],
            'created_by' => $users['hendra@dd.or.id']->id,
            'verified_by' => $sekretaris->id,
            'verified_at' => now()->subWeek(),
            'completed_at' => now()->subDays(5),
        ]);
        $surats[] = $s;

        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => null, 'status_ke_id' => $status['received'], 'dilakukan_oleh_id' => $users['hendra@dd.or.id']->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['received'], 'status_ke_id' => $status['verified'], 'dilakukan_oleh_id' => $sekretaris->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['verified'], 'status_ke_id' => $status['completed'], 'dilakukan_oleh_id' => $sekretaris->id, 'catatan' => 'Tidak relevan, langsung selesai']);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['completed'], 'status_ke_id' => $status['archived'], 'dilakukan_oleh_id' => $admin->id, 'catatan' => 'Diarsipkan']);

        // Surat 9: baru diterima (CC Security)
        $s = SuratMasuk::create([
            'nomor_agenda' => 'AG-20260731-0009',
            'nomor_surat' => 'SEC-045/VII/2026',
            'pengirim' => 'Badan Intelijen Negara',
            'perihal' => 'Permintaan Data Keamanan Lingkungan',
            'tanggal_surat' => '2026-07-30',
            'tanggal_diterima' => '2026-07-31',
            'klasifikasi_id' => $klasifikasi['RAHASIA'],
            'status_id' => $status['received'],
            'is_rahasia' => true,
            'created_by' => $users['hendra@dd.or.id']->id,
        ]);
        $surats[] = $s;

        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => null, 'status_ke_id' => $status['received'], 'dilakukan_oleh_id' => $users['hendra@dd.or.id']->id]);

        // Surat 10: disposisi ke Direktorat Komunikasi (dalam progress)
        $s = SuratMasuk::create([
            'nomor_agenda' => 'AG-20260730-0010',
            'nomor_surat' => '077/KOM/VII/2026',
            'pengirim' => 'Media Group',
            'perihal' => 'Permohonan Wawancara Eksklusif dengan Pimpinan Yayasan',
            'tanggal_surat' => '2026-07-28',
            'tanggal_diterima' => '2026-07-30',
            'klasifikasi_id' => $klasifikasi['UMUM'],
            'status_id' => $status['in_progress'],
            'created_by' => $users['dewi@dd.or.id']->id,
            'verified_by' => $users['dewi@dd.or.id']->id,
            'verified_at' => now()->subDays(2),
        ]);
        $surats[] = $s;

        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => null, 'status_ke_id' => $status['received'], 'dilakukan_oleh_id' => $users['dewi@dd.or.id']->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['received'], 'status_ke_id' => $status['verified'], 'dilakukan_oleh_id' => $users['dewi@dd.or.id']->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['verified'], 'status_ke_id' => $status['forwarded_to_leadership'], 'dilakukan_oleh_id' => $users['dewi@dd.or.id']->id, 'atas_nama_pimpinan_id' => $pimpinan->id]);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['forwarded_to_leadership'], 'status_ke_id' => $status['assigned'], 'dilakukan_oleh_id' => $pimpinan->id, 'catatan' => 'Disposisi ke Komunikasi']);
        RiwayatStatus::create(['surat_masuk_id' => $s->id, 'status_dari_id' => $status['assigned'], 'status_ke_id' => $status['in_progress'], 'dilakukan_oleh_id' => $users['fitri@dd.or.id']->id, 'catatan' => 'Menjadwalkan wawancara']);

        Disposisi::create([
            'surat_masuk_id' => $s->id,
            'dari_user_id' => $users['dewi@dd.or.id']->id,
            'kepada_user_id' => $users['fitri@dd.or.id']->id,
            'jenis' => 'ke_pelaksana',
            'atas_nama_pimpinan_id' => $pimpinan->id,
            'catatan' => 'Atur jadwal wawancara dengan pimpinan.',
            'tanggal_deadline' => '2026-08-05',
        ]);

        // Surat keluar
        SuratKeluar::create([
            'nomor_surat' => 'SK-001/DDF/VII/2026',
            'tujuan_surat' => 'Kementerian Sosial RI',
            'perihal' => 'Konfirmasi Kehadiran Rapat Koordinasi',
            'tanggal_surat' => '2026-07-30',
            'klasifikasi_id' => $klasifikasi['UMUM'],
            'surat_masuk_id' => $surats[0]->id,
            'created_by' => $sekretaris->id,
        ]);

        SuratKeluar::create([
            'nomor_surat' => 'SK-002/DDF/VII/2026',
            'tujuan_surat' => 'Universitas Indonesia',
            'perihal' => 'Persetujuan Program Magang',
            'tanggal_surat' => '2026-07-31',
            'klasifikasi_id' => $klasifikasi['SDM'],
            'surat_masuk_id' => $surats[2]->id,
            'created_by' => $sekretaris->id,
        ]);

        SuratKeluar::create([
            'nomor_surat' => 'SK-003/DDF/VII/2026',
            'tujuan_surat' => 'Global Charity Foundation',
            'perihal' => 'Penerimaan Proposal Kerjasama Beasiswa',
            'tanggal_surat' => '2026-07-29',
            'klasifikasi_id' => $klasifikasi['PROGRAM'],
            'surat_masuk_id' => $surats[5]->id,
            'created_by' => $users['rina@dd.or.id']->id,
        ]);

        $totalUsers = User::count();
        $this->command->info("Test data seeded: {$totalUsers} users, " . count($surats) . ' surat masuk, 3 surat keluar');
    }
}
