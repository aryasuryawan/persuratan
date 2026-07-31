<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure all unique direktorats exist
        $direktoratMap = [];
        $direktorats = [
            'Bendahara', 'Sekretariat', 'Pimpinan', 'Program', 'Keuangan',
            'SDM', 'Fundraising', 'IT', 'Komunikasi', 'Keamanan',
            'Sales', 'Accounting', 'Product Management',
        ];
        foreach ($direktorats as $nama) {
            $row = DB::table('direktorats')->where('nama', $nama)->first();
            if (!$row) {
                $kode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nama), 0, 4));
                $id = DB::table('direktorats')->insertGetId([
                    'nama' => $nama, 'kode' => $kode, 'aktif' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $direktoratMap[$nama] = $id;
            } else {
                $direktoratMap[$nama] = $row->id;
            }
        }

        // Ensure all unique divisis exist
        $divisiMap = [];
        $divisiData = [
            ['nama' => 'Administrasi & Umum', 'kode' => 'ADMU', 'dir' => 'Bendahara'],
            ['nama' => 'Admin', 'kode' => 'ADM', 'dir' => 'Sekretariat'],
            ['nama' => 'Sekretariat', 'kode' => 'SEK', 'dir' => 'Sekretariat'],
            ['nama' => 'Sekretariat 2', 'kode' => 'SEK2', 'dir' => 'Sekretariat'],
            ['nama' => 'Pimpinan', 'kode' => 'PIM', 'dir' => 'Pimpinan'],
            ['nama' => 'Direktorat Program', 'kode' => 'DPRG', 'dir' => 'Program'],
            ['nama' => 'Direktorat Keuangan', 'kode' => 'DKEU', 'dir' => 'Keuangan'],
            ['nama' => 'Direktorat SDM', 'kode' => 'DSDM', 'dir' => 'SDM'],
            ['nama' => 'Direktorat Fundraising', 'kode' => 'DFUND', 'dir' => 'Fundraising'],
            ['nama' => 'Direktorat IT', 'kode' => 'DIT', 'dir' => 'IT'],
            ['nama' => 'Direktorat Komunikasi', 'kode' => 'DKOM', 'dir' => 'Komunikasi'],
            ['nama' => 'CC Security', 'kode' => 'CCS', 'dir' => 'Keamanan'],
            ['nama' => 'Business Development', 'kode' => 'BD', 'dir' => 'Sales'],
            ['nama' => 'Engineering', 'kode' => 'ENG', 'dir' => 'Sales'],
            ['nama' => 'Finance', 'kode' => 'FIN', 'dir' => 'Sales'],
            ['nama' => 'Quality Control', 'kode' => 'QC', 'dir' => 'Accounting'],
            ['nama' => 'Research', 'kode' => 'RES', 'dir' => 'Product Management'],
        ];
        foreach ($divisiData as $d) {
            $row = DB::table('divisis')->where('nama', $d['nama'])->first();
            if (!$row) {
                $id = DB::table('divisis')->insertGetId([
                    'direktorat_id' => $direktoratMap[$d['dir']],
                    'nama' => $d['nama'], 'kode' => $d['kode'], 'aktif' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $divisiMap[$d['nama']] = $id;
            } else {
                $divisiMap[$d['nama']] = $row->id;
            }
        }

        // Ensure departemen exists
        $deptRow = DB::table('departemens')->where('nama', 'Report Manajemen & Administrasi Lembaga')->first();
        if (!$deptRow) {
            DB::table('departemens')->insert([
                'divisi_id' => $divisiMap['Administrasi & Umum'],
                'nama' => 'Report Manajemen & Administrasi Lembaga',
                'kode' => 'REA', 'aktif' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Ensure all unique struktur entries exist
        $strukturData = [
            ['kode' => 'SULSEL', 'nama' => 'Dompet Dhuafa Cabang Sulawesi Selatan'],
            ['kode' => 'YWIB', 'nama' => 'Yayasan Wirausaha Indonesia Berdaya'],
            ['kode' => 'Ka.Pengurus', 'nama' => 'Ketua Pengurus'],
            ['kode' => 'DLTD', 'nama' => 'Direktorat Dakwah & LTD'],
            ['kode' => 'PROG', 'nama' => 'Direktorat Program'],
            ['kode' => 'FO', 'nama' => 'Direktorat Keuangan dan Operasional'],
            ['kode' => 'WAF', 'nama' => 'Direktorat Mobilisasi Wakaf'],
            ['kode' => 'ZIS', 'nama' => 'Direktorat Mobilisasi ZIS'],
            ['kode' => 'DIRUT', 'nama' => 'Direktur Utama'],
        ];
        foreach ($strukturData as $s) {
            $row = DB::table('strukturs')->where('kode', $s['kode'])->first();
            if (!$row) {
                DB::table('strukturs')->insert([
                    'nama' => $s['nama'], 'kode' => $s['kode'], 'aktif' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Master data seeded.');
    }
}
