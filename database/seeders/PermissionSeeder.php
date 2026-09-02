<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Surat Masuk
            ['kode' => 'surat-masuk.index', 'label' => 'Lihat Daftar Surat Masuk', 'group' => 'surat_masuk', 'description' => 'Akses halaman daftar surat masuk'],
            ['kode' => 'surat-masuk.create', 'label' => 'Buat Surat Masuk', 'group' => 'surat_masuk', 'description' => 'Membuat surat masuk baru'],
            ['kode' => 'surat-masuk.show', 'label' => 'Detail Surat Masuk', 'group' => 'surat_masuk', 'description' => 'Melihat detail surat masuk'],
            ['kode' => 'surat-masuk.edit', 'label' => 'Edit Surat Masuk', 'group' => 'surat_masuk', 'description' => 'Mengedit surat masuk'],
            ['kode' => 'surat-masuk.delete', 'label' => 'Hapus Surat Masuk', 'group' => 'surat_masuk', 'description' => 'Menghapus surat masuk'],
            ['kode' => 'surat-masuk.verify', 'label' => 'Verifikasi Surat Masuk', 'group' => 'surat_masuk', 'description' => 'Verifikasi surat masuk (sekretaris/admin)'],
            ['kode' => 'surat-masuk.assign-akses', 'label' => 'Assign Akses Surat Masuk', 'group' => 'surat_masuk', 'description' => 'Memberi akses manual ke surat masuk restricted'],

            // Surat Keluar
            ['kode' => 'surat-keluar.index', 'label' => 'Lihat Daftar Surat Keluar', 'group' => 'surat_keluar', 'description' => 'Akses halaman daftar surat keluar'],
            ['kode' => 'surat-keluar.create', 'label' => 'Buat Surat Keluar', 'group' => 'surat_keluar', 'description' => 'Membuat surat keluar baru'],
            ['kode' => 'surat-keluar.show', 'label' => 'Detail Surat Keluar', 'group' => 'surat_keluar', 'description' => 'Melihat detail surat keluar'],
            ['kode' => 'surat-keluar.edit', 'label' => 'Edit Surat Keluar', 'group' => 'surat_keluar', 'description' => 'Mengedit surat keluar'],
            ['kode' => 'surat-keluar.delete', 'label' => 'Hapus Surat Keluar', 'group' => 'surat_keluar', 'description' => 'Menghapus surat keluar'],

            // Disposisi
            ['kode' => 'disposisi.index', 'label' => 'Lihat Daftar Disposisi', 'group' => 'disposisi', 'description' => 'Akses halaman daftar disposisi'],
            ['kode' => 'disposisi.create', 'label' => 'Buat Disposisi', 'group' => 'disposisi', 'description' => 'Membuat disposisi baru'],
            ['kode' => 'disposisi.update', 'label' => 'Update Disposisi', 'group' => 'disposisi', 'description' => 'Mengupdate status disposisi'],
            ['kode' => 'disposisi.delete', 'label' => 'Hapus Disposisi', 'group' => 'disposisi', 'description' => 'Menghapus disposisi'],
            ['kode' => 'disposisi.followup', 'label' => 'Follow Up Disposisi', 'group' => 'disposisi', 'description' => 'Membuat follow up disposisi'],
            ['kode' => 'disposisi.pindah', 'label' => 'Pindah Disposisi', 'group' => 'disposisi', 'description' => 'Memindahkan disposisi ke user lain'],

            // Surat Keputusan
            ['kode' => 'surat-keputusan.index', 'label' => 'Lihat Daftar Surat Keputusan', 'group' => 'surat_keputusan', 'description' => 'Akses halaman daftar SK'],
            ['kode' => 'surat-keputusan.create', 'label' => 'Buat Surat Keputusan', 'group' => 'surat_keputusan', 'description' => 'Membuat SK baru'],
            ['kode' => 'surat-keputusan.show', 'label' => 'Detail Surat Keputusan', 'group' => 'surat_keputusan', 'description' => 'Melihat detail SK'],
            ['kode' => 'surat-keputusan.edit', 'label' => 'Edit Surat Keputusan', 'group' => 'surat_keputusan', 'description' => 'Mengedit SK'],
            ['kode' => 'surat-keputusan.delete', 'label' => 'Hapus Surat Keputusan', 'group' => 'surat_keputusan', 'description' => 'Menghapus SK'],
            ['kode' => 'surat-keputusan.assign-akses', 'label' => 'Assign Akses SK', 'group' => 'surat_keputusan', 'description' => 'Memberi akses manual ke SK rahasia'],

            // Komite Persetujuan
            ['kode' => 'komite-persetujuan.index', 'label' => 'Lihat Daftar Komite', 'group' => 'komite_persetujuan', 'description' => 'Akses halaman daftar komite persetujuan'],
            ['kode' => 'komite-persetujuan.create', 'label' => 'Buat Komite', 'group' => 'komite_persetujuan', 'description' => 'Membuat komite persetujuan baru'],
            ['kode' => 'komite-persetujuan.show', 'label' => 'Detail Komite', 'group' => 'komite_persetujuan', 'description' => 'Melihat detail komite'],
            ['kode' => 'komite-persetujuan.edit', 'label' => 'Edit Komite', 'group' => 'komite_persetujuan', 'description' => 'Mengedit komite'],
            ['kode' => 'komite-persetujuan.delete', 'label' => 'Hapus Komite', 'group' => 'komite_persetujuan', 'description' => 'Menghapus komite'],
            ['kode' => 'komite-persetujuan.assign-akses', 'label' => 'Assign Akses Komite', 'group' => 'komite_persetujuan', 'description' => 'Memberi akses manual ke komite rahasia'],

            // Notulensi Rapim
            ['kode' => 'notulensi-rapim.index', 'label' => 'Lihat Daftar Notulensi', 'group' => 'notulensi_rapim', 'description' => 'Akses halaman daftar notulensi rapim'],
            ['kode' => 'notulensi-rapim.create', 'label' => 'Buat Notulensi', 'group' => 'notulensi_rapim', 'description' => 'Membuat notulensi baru'],
            ['kode' => 'notulensi-rapim.show', 'label' => 'Detail Notulensi', 'group' => 'notulensi_rapim', 'description' => 'Melihat detail notulensi'],
            ['kode' => 'notulensi-rapim.edit', 'label' => 'Edit Notulensi', 'group' => 'notulensi_rapim', 'description' => 'Mengedit notulensi'],
            ['kode' => 'notulensi-rapim.delete', 'label' => 'Hapus Notulensi', 'group' => 'notulensi_rapim', 'description' => 'Menghapus notulensi'],
            ['kode' => 'notulensi-rapim.assign-akses', 'label' => 'Assign Akses Notulensi', 'group' => 'notulensi_rapim', 'description' => 'Memberi akses manual ke notulensi rahasia'],

            // Arsip
            ['kode' => 'arsip.akses.list', 'label' => 'Lihat Daftar Akses Arsip', 'group' => 'arsip', 'description' => 'Melihat daftar user yang punya akses arsip'],
            ['kode' => 'arsip.akses.assign', 'label' => 'Assign Akses Arsip', 'group' => 'arsip', 'description' => 'Memberi akses manual ke arsip rahasia'],
            ['kode' => 'arsip.akses.remove', 'label' => 'Hapus Akses Arsip', 'group' => 'arsip', 'description' => 'Menghapus akses manual arsip'],

            // Tracking
            ['kode' => 'tracking.index', 'label' => 'Lihat Tracking Surat', 'group' => 'tracking', 'description' => 'Akses halaman tracking surat'],

            // AI Assistant
            ['kode' => 'ai-assistant.use', 'label' => 'Gunakan AI Assistant', 'group' => 'ai_assistant', 'description' => 'Boleh bertanya ke floating AI assistant'],

            // Master
            ['kode' => 'master.users.index', 'label' => 'Lihat Daftar User', 'group' => 'master', 'description' => 'Akses master user'],
            ['kode' => 'master.users.create', 'label' => 'Buat User', 'group' => 'master', 'description' => 'Membuat user baru'],
            ['kode' => 'master.users.edit', 'label' => 'Edit User', 'group' => 'master', 'description' => 'Mengedit user'],
            ['kode' => 'master.users.delete', 'label' => 'Hapus User', 'group' => 'master', 'description' => 'Menghapus user'],
            ['kode' => 'master.roles.index', 'label' => 'Lihat Daftar Role', 'group' => 'master', 'description' => 'Akses master role'],
            ['kode' => 'master.roles.create', 'label' => 'Buat Role', 'group' => 'master', 'description' => 'Membuat role baru'],
            ['kode' => 'master.roles.edit', 'label' => 'Edit Role', 'group' => 'master', 'description' => 'Mengedit role'],
            ['kode' => 'master.roles.delete', 'label' => 'Hapus Role', 'group' => 'master', 'description' => 'Menghapus role'],
            ['kode' => 'master.roles.permissions', 'label' => 'Kelola Permission Role', 'group' => 'master', 'description' => 'Mengatur permission per role'],
            ['kode' => 'master.klasifikasi.index', 'label' => 'Lihat Klasifikasi', 'group' => 'master', 'description' => 'Akses master klasifikasi'],
            ['kode' => 'master.klasifikasi.create', 'label' => 'Buat Klasifikasi', 'group' => 'master', 'description' => 'Membuat klasifikasi baru'],
            ['kode' => 'master.klasifikasi.edit', 'label' => 'Edit Klasifikasi', 'group' => 'master', 'description' => 'Mengedit klasifikasi'],
            ['kode' => 'master.klasifikasi.delete', 'label' => 'Hapus Klasifikasi', 'group' => 'master', 'description' => 'Menghapus klasifikasi'],
            ['kode' => 'master.topik.index', 'label' => 'Lihat Topik', 'group' => 'master', 'description' => 'Akses master topik'],
            ['kode' => 'master.topik.create', 'label' => 'Buat Topik', 'group' => 'master', 'description' => 'Membuat topik baru'],
            ['kode' => 'master.topik.edit', 'label' => 'Edit Topik', 'group' => 'master', 'description' => 'Mengedit topik'],
            ['kode' => 'master.topik.delete', 'label' => 'Hapus Topik', 'group' => 'master', 'description' => 'Menghapus topik'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['kode' => $perm['kode']], $perm);
        }

        $this->assignDefaultPermissions();
    }

    private function assignDefaultPermissions(): void
    {
        $admin = \App\Models\Role::where('kode', 'admin')->first();
        $sekretaris = \App\Models\Role::where('kode', 'sekretaris')->first();
        $pimpinan = \App\Models\Role::where('kode', 'pimpinan')->first();
        $unitLain = \App\Models\Role::where('kode', 'unit_lain')->first();
        $ccSecurity = \App\Models\Role::where('kode', 'cc_security')->first();

        if ($admin) {
            $allPerms = Permission::pluck('id')->toArray();
            $admin->permissions()->syncWithoutDetaching($allPerms);
        }

        if ($sekretaris) {
            $sekretarisPerms = Permission::whereIn('group', ['surat_masuk', 'surat_keluar', 'disposisi', 'surat_keputusan', 'komite_persetujuan', 'notulensi_rapim', 'arsip', 'tracking', 'master', 'ai_assistant'])->pluck('id')->toArray();
            $sekretaris->permissions()->syncWithoutDetaching($sekretarisPerms);
        }

        if ($pimpinan) {
            $pimpinanPerms = Permission::whereIn('kode', [
                'surat-masuk.index', 'surat-masuk.show',
                'surat-keluar.index', 'surat-keluar.show',
                'disposisi.index', 'disposisi.show',
                'surat-keputusan.index', 'surat-keputusan.show',
                'komite-persetujuan.index', 'komite-persetujuan.show',
                'notulensi-rapim.index', 'notulensi-rapim.show',
                'tracking.index',
                'ai-assistant.use',
            ])->pluck('id')->toArray();
            $pimpinan->permissions()->syncWithoutDetaching($pimpinanPerms);
        }

        if ($unitLain) {
            $unitPerms = Permission::whereIn('kode', [
                'surat-masuk.index', 'surat-masuk.show',
                'surat-keluar.index', 'surat-keluar.show',
                'disposisi.index', 'disposisi.show', 'disposisi.followup',
                'surat-keputusan.index', 'surat-keputusan.show',
                'komite-persetujuan.index', 'komite-persetujuan.show',
                'notulensi-rapim.index', 'notulensi-rapim.show',
                'tracking.index',
                'ai-assistant.use',
            ])->pluck('id')->toArray();
            $unitLain->permissions()->syncWithoutDetaching($unitPerms);
        }

        if ($ccSecurity) {
            $ccPerms = Permission::whereIn('kode', [
                'surat-masuk.index', 'surat-masuk.show',
                'surat-keluar.index', 'surat-keluar.show',
                'disposisi.index', 'disposisi.show',
                'surat-keputusan.index', 'surat-keputusan.show',
                'komite-persetujuan.index', 'komite-persetujuan.show',
                'notulensi-rapim.index', 'notulensi-rapim.show',
                'tracking.index',
                'ai-assistant.use',
            ])->pluck('id')->toArray();
            $ccSecurity->permissions()->syncWithoutDetaching($ccPerms);
        }
    }
}