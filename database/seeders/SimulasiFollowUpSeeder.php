<?php

namespace Database\Seeders;

use App\Actions\BuatDisposisi;
use App\Models\Disposisi;
use App\Models\Klasifikasi;
use App\Models\Role;
use App\Models\Setting;
use App\Models\StatusSurat;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SimulasiFollowUpSeeder extends Seeder
{
    public function run(): void
    {
        $this->configureMailpit();

        $sekretaris = $this->user('sekretaris', 'Sekretaris Simulasi', 'sekretaris@test.local');
        $pimpinan = $this->user('pimpinan', 'Pimpinan Simulasi', 'pimpinan@test.local', 'Ketua Pengurus');
        $pelaksana = $this->user('unit_lain', 'Pelaksana Simulasi', 'pelaksana@test.local', 'Staf Divisi Program');

        $surat = SuratMasuk::create([
            'nomor_agenda' => 'AG-SIM-' . now()->format('Ymd') . '-001',
            'nomor_surat' => '012/PMT/MMM/VIII/2026',
            'pengirim' => 'PT Mitra Sejahtera',
            'perihal' => 'Penawaran Kerja Sama Program Pemberdayaan Masyarakat',
            'tanggal_surat' => today(),
            'tanggal_diterima' => today(),
            'klasifikasi_id' => Klasifikasi::firstOrCreate(
                ['kode' => 'UMUM'],
                ['nama' => 'Umum', 'color' => 'secondary', 'is_rahasia' => false]
            )->id,
            'status_id' => StatusSurat::firstOrCreate(
                ['kode' => 'received'],
                ['label' => 'Diterima', 'urutan' => 1]
            )->id,
            'created_by' => $sekretaris->id,
        ]);

        $this->command->info('Surat masuk simulasi: ' . $surat->nomor_agenda . ' - ' . $surat->perihal);

        try {
            $disposisi = app(BuatDisposisi::class)->execute(
                surat: $surat,
                dariUser: $sekretaris,
                jenis: 'ke_pelaksana',
                atasNamaPimpinan: $pimpinan,
                kepadaUser: $pelaksana,
                catatan: 'Mohon ditindaklanjuti sesuai prosedur.',
                catatanPimpinan: 'Ditunggu laporan follow up-nya.',
                tanggalDeadline: now()->addDays(7)->toDateString(),
                instruksi: ['Koordinasi dengan pihak mitra', 'Siapkan draft MoU', 'Laporkan progres'],
                disposisiKepada: [$pelaksana->name],
            );
        } catch (\Throwable $e) {
            $this->command->error('Gagal membuat disposisi / mengirim email: ' . $e->getMessage());

            return;
        }

        $link = url('/follow-up/' . $disposisi->uuid);

        $this->command->info('Disposisi #' . $disposisi->id . ' ke ' . $pelaksana->name . ' dibuat.');
        $this->command->info('Email notifikasi + magic link dikirim ke ' . $pelaksana->email . ' (lihat di Mailpit http://127.0.0.1:8025).');
        $this->command->newLine();
        $this->command->info('Magic link follow-up (tanpa login):');
        $this->command->line($link);
    }

    private function configureMailpit(): void
    {
        Setting::set('smtp_enabled', '1');
        Setting::set('smtp_driver', 'smtp');
        Setting::set('smtp_host', '127.0.0.1');
        Setting::set('smtp_port', '1025');
        Setting::set('smtp_username', '');
        Setting::set('smtp_password', '');
        Setting::set('smtp_encryption', 'none');
        Setting::set('smtp_from_address', 'persuratan@test.local');
        Setting::set('smtp_from_name', 'SiMPEL Simulasi');

        $this->command->info('Konfigurasi email diarahkan ke Mailpit (127.0.0.1:1025).');
    }

    private function user(string $roleKode, string $name, string $email, string $jabatan = null): User
    {
        $role = Role::firstOrCreate(['kode' => $roleKode], ['label' => ucwords(str_replace('_', ' ', $roleKode))]);

        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'role_id' => $role->id,
                'jabatan' => $jabatan,
                'is_active' => true,
            ]
        );
    }
}
