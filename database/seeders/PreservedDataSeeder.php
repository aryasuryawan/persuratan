<?php

namespace Database\Seeders;

use App\Models\Direktorat;
use App\Models\Divisi;
use App\Models\KodeSurat;
use App\Models\Role;
use App\Models\Struktur;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PreservedDataSeeder extends Seeder
{
    public function run(): void
    {
        // === Kode Surat (dipertahankan) ===
        $kodeSurat = [
            ['kode' => 'PEMB', 'nama' => 'Permohonan Narasumber & Pembicara'],
            ['kode' => 'IM', 'nama' => 'Internal Memo'],
            ['kode' => 'PKS', 'nama' => 'Surat Perjanjian Kerjasama'],
            ['kode' => 'MOU', 'nama' => 'Nota Kesepahaman'],
            ['kode' => 'SU', 'nama' => 'Surat Undangan'],
            ['kode' => 'ST', 'nama' => 'Surat Tugas'],
            ['kode' => 'SKET', 'nama' => 'Surat Keterangan'],
            ['kode' => 'SPT', 'nama' => 'Surat Pelimpahan Tugas'],
            ['kode' => 'SKU', 'nama' => 'Surat Kuasa'],
            ['kode' => 'SK', 'nama' => 'Surat Keputusan'],
            ['kode' => 'SR', 'nama' => 'Surat Rahasia'],
            ['kode' => 'SKO', 'nama' => 'Surat Konfidensial'],
            ['kode' => 'SE', 'nama' => 'Surat Edaran Lembaga'],
            ['kode' => 'SUM', 'nama' => 'Surat Pemberitahuan/Umum'],
            ['kode' => 'S.Kel', 'nama' => 'Surat Keluar'],
            ['kode' => 'PROP', 'nama' => 'Proposal'],
        ];
        foreach ($kodeSurat as $ks) {
            KodeSurat::updateOrCreate(['kode' => $ks['kode']], [
                'nama' => $ks['nama'],
                'aktif' => true,
            ]);
        }

        // === Struktur (dipertahankan) ===
        $struktur = [
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
        foreach ($struktur as $st) {
            Struktur::updateOrCreate(['kode' => $st['kode']], [
                'nama' => $st['nama'],
                'aktif' => true,
            ]);
        }

        // === User uji coba (dipertahankan) ===
        $users = [
            ['name' => 'Administrator', 'email' => 'admin@dd.or.id', 'title' => null, 'role' => 'admin', 'dir' => 'Sekretariat', 'div' => 'Admin'],
            ['name' => 'Sekretaris', 'email' => 'sekretaris@dd.or.id', 'title' => null, 'role' => 'sekretaris', 'dir' => 'Sekretariat', 'div' => 'Sekretariat'],
            ['name' => 'Pimpinan', 'email' => 'pimpinan@dd.or.id', 'title' => null, 'role' => 'pimpinan', 'dir' => 'Pimpinan', 'div' => 'Pimpinan'],
            ['name' => 'Thatcher Keel', 'email' => 'tkeel@blogger.com', 'title' => 'VP Sales', 'role' => 'cc_security', 'dir' => 'Sales', 'div' => 'Business Development'],
            ['name' => 'Dyann Escala', 'email' => 'descala@usatoday.com', 'title' => 'Mechanical Systems Engineer', 'role' => 'unit_lain', 'dir' => 'Sales', 'div' => 'Engineering'],
            ['name' => 'Avivah Mugleston', 'email' => 'amugleston@intel.com', 'title' => 'Actuary', 'role' => 'unit_lain', 'dir' => 'Sales', 'div' => 'Finance'],
            ['name' => 'Arlie Armstead', 'email' => 'aarmsteadi@yellowpages.com', 'title' => 'VP Quality Control', 'role' => 'pimpinan', 'dir' => 'Accounting', 'div' => 'Quality Control'],
            ['name' => 'Tessie Curzon', 'email' => 'tcurzonj@hp.com', 'title' => 'Research Nurse', 'role' => 'unit_lain', 'dir' => 'Product Management', 'div' => 'Research'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@dd.or.id', 'title' => null, 'role' => 'unit_lain', 'dir' => 'Program', 'div' => 'Direktorat Program'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti@dd.or.id', 'title' => null, 'role' => 'unit_lain', 'dir' => 'Keuangan', 'div' => 'Direktorat Keuangan'],
            ['name' => 'Bambang Wijaya', 'email' => 'bambang@dd.or.id', 'title' => null, 'role' => 'unit_lain', 'dir' => 'SDM', 'div' => 'Direktorat SDM'],
            ['name' => 'Rina Marlina', 'email' => 'rina@dd.or.id', 'title' => null, 'role' => 'unit_lain', 'dir' => 'Fundraising', 'div' => 'Direktorat Fundraising'],
            ['name' => 'Dedi Kurniawan', 'email' => 'dedi@dd.or.id', 'title' => null, 'role' => 'unit_lain', 'dir' => 'IT', 'div' => 'Direktorat IT'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri@dd.or.id', 'title' => 'Officer', 'role' => 'unit_lain', 'dir' => 'Komunikasi', 'div' => 'Direktorat Komunikasi'],
            ['name' => 'Hendra Gunawan', 'email' => 'hendra@dd.or.id', 'title' => null, 'role' => 'cc_security', 'dir' => 'Keamanan', 'div' => 'CC Security'],
            ['name' => 'Dewi Sartika', 'email' => 'dewi@dd.or.id', 'title' => null, 'role' => 'sekretaris', 'dir' => 'Sekretariat', 'div' => 'Sekretariat 2'],
        ];

        $roles = Role::pluck('id', 'kode');

        foreach ($users as $u) {
            $dir = Direktorat::where('nama', $u['dir'])->first();
            $div = Divisi::where('nama', $u['div'])->first();

            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'title' => $u['title'],
                    'role_id' => $roles[$u['role']],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'direktorat_id' => $dir?->id,
                    'divisi_id' => $div?->id,
                ]
            );
        }

        $this->command->info('Preserved data seeded: users, kode surat, struktur.');
    }
}
