<?php

namespace Tests\Feature;

use App\Models\Klasifikasi;
use App\Models\Role;
use App\Models\StatusSurat;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisposisiEmailCcTest extends TestCase
{
    use RefreshDatabase;

    private Role $sekretarisRole;
    private Role $unitLainRole;
    private Klasifikasi $normal;

    private EmailServiceSpy $emailSpy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sekretarisRole = Role::create(['kode' => 'sekretaris', 'label' => 'Sekretaris']);
        $this->unitLainRole = Role::create(['kode' => 'unit_lain', 'label' => 'Unit Lain']);

        StatusSurat::create(['kode' => 'received', 'label' => 'Diterima', 'urutan' => 1]);
        StatusSurat::create(['kode' => 'assigned', 'label' => 'Didisposisikan', 'urutan' => 2]);
        StatusSurat::create(['kode' => 'handled_by_leadership', 'label' => 'Ditangani Pimpinan', 'urutan' => 3]);

        $this->normal = Klasifikasi::create([
            'kode' => 'normal',
            'nama' => 'Normal',
            'is_rahasia' => false,
        ]);

        $this->emailSpy = new EmailServiceSpy;
        $this->app->instance(EmailService::class, $this->emailSpy);
    }

    private function user(Role $role, array $extra = []): User
    {
        return User::factory()->create(array_merge(['role_id' => $role->id], $extra));
    }

    private function createSuratMasuk(User $creator): SuratMasuk
    {
        return SuratMasuk::create([
            'nomor_agenda' => 'AG-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            'pengirim' => 'PT Mitra Sejahtera',
            'perihal' => 'Penawaran Kerja Sama Proyek',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $this->normal->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $creator->id,
        ]);
    }

    public function test_subject_berformat_disposisi_jabatan_atas_nama_uppercase(): void
    {
        $sekretaris = $this->user($this->sekretarisRole);
        $pimpinan = $this->user($this->unitLainRole, ['jabatan' => 'Ketua Pengurus']);
        $pelaksana = $this->user($this->unitLainRole);
        $surat = $this->createSuratMasuk($sekretaris);

        $this->actingAs($sekretaris)->post('/disposisi', [
            'surat_masuk_id' => $surat->id,
            'jenis' => 'ke_pelaksana',
            'atas_nama_pimpinan_id' => $pimpinan->id,
            'disposisi_kepada' => [$pelaksana->name],
        ]);

        $this->assertCount(1, $this->emailSpy->calls);
        $this->assertSame(
            'DISPOSISI KETUA PENGURUS: Penawaran Kerja Sama Proyek',
            $this->emailSpy->calls[0]['subject']
        );
    }

    public function test_cc_diterima_bersama_email_notifikasi_dan_dideduplikasi(): void
    {
        $sekretaris = $this->user($this->sekretarisRole);
        $pimpinan = $this->user($this->unitLainRole, ['jabatan' => 'Ketua Pengurus']);
        $pelaksana = $this->user($this->unitLainRole);
        $surat = $this->createSuratMasuk($sekretaris);

        $this->actingAs($sekretaris)->post('/disposisi', [
            'surat_masuk_id' => $surat->id,
            'jenis' => 'ke_pelaksana',
            'atas_nama_pimpinan_id' => $pimpinan->id,
            'disposisi_kepada' => [$pelaksana->name],
            'cc' => [$pelaksana->email, 'EKSTERNAL@mail.com'],
            'cc_free' => 'cc2@mail.com, eksternal@mail.com',
        ]);

        $this->assertCount(1, $this->emailSpy->calls);
        $call = $this->emailSpy->calls[0];

        $this->assertSame(strtolower($pelaksana->email), $call['to']);
        $this->assertSame(['eksternal@mail.com', 'cc2@mail.com'], $call['cc']);
    }

    public function test_cc_kosong_tidak_mengubah_pengiriman(): void
    {
        $sekretaris = $this->user($this->sekretarisRole);
        $pimpinan = $this->user($this->unitLainRole, ['jabatan' => 'Ketua Pengurus']);
        $pelaksana = $this->user($this->unitLainRole);
        $surat = $this->createSuratMasuk($sekretaris);

        $this->actingAs($sekretaris)->post('/disposisi', [
            'surat_masuk_id' => $surat->id,
            'jenis' => 'ke_pelaksana',
            'atas_nama_pimpinan_id' => $pimpinan->id,
            'disposisi_kepada' => [$pelaksana->name],
        ]);

        $this->assertCount(1, $this->emailSpy->calls);
        $this->assertSame([], $this->emailSpy->calls[0]['cc']);
    }
}

class EmailServiceSpy extends EmailService
{
    public array $calls = [];

    public function send(string $to, string $subject, string $body, array $data = [], array $attachments = [], array $cc = []): array
    {
        $this->calls[] = compact('to', 'subject', 'body', 'data', 'attachments', 'cc');

        return ['success' => true, 'log_id' => 0];
    }
}
