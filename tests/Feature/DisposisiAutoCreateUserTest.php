<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\Klasifikasi;
use App\Models\Role;
use App\Models\StatusSurat;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisposisiAutoCreateUserTest extends TestCase
{
    use RefreshDatabase;

    private Role $sekretarisRole;
    private Role $unitLainRole;
    private Klasifikasi $normal;

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
    }

    private function user(Role $role): User
    {
        return User::factory()->create(['role_id' => $role->id]);
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

    public function test_freetext_nama_email_belum_terdaftar_dibuat_sebagai_user_baru(): void
    {
        $sekretaris = $this->user($this->sekretarisRole);
        $surat = $this->createSuratMasuk($sekretaris);

        $response = $this->actingAs($sekretaris)->post('/disposisi', [
            'surat_masuk_id' => $surat->id,
            'jenis' => 'ke_pelaksana',
            'disposisi_kepada_free' => ['Budi Santoso'],
            'disposisi_kepada_free_email' => ['budi.santoso@mail.com'],
        ]);

        $response->assertRedirect(route('penerimaan-surat.show', $surat->uuid));
        $response->assertSessionHas('success');

        $userBaru = User::where('email', 'budi.santoso@mail.com')->first();
        $this->assertNotNull($userBaru);
        $this->assertSame('Budi Santoso', $userBaru->name);
        $this->assertSame($this->unitLainRole->id, $userBaru->role_id);

        $disposisi = Disposisi::where('surat_masuk_id', $surat->id)->first();
        $this->assertNotNull($disposisi);
        $this->assertSame($userBaru->id, $disposisi->kepada_user_id);
        $this->assertContains('Budi Santoso', $disposisi->disposisi_kepada);
    }

    public function test_freetext_tanpa_email_tidak_membuat_user_baru(): void
    {
        $sekretaris = $this->user($this->sekretarisRole);
        $pelaksana = $this->user($this->unitLainRole);
        $surat = $this->createSuratMasuk($sekretaris);

        $this->actingAs($sekretaris)->post('/disposisi', [
            'surat_masuk_id' => $surat->id,
            'jenis' => 'ke_pelaksana',
            'disposisi_kepada' => [$pelaksana->name],
            'disposisi_kepada_free' => ['Divisi Keuangan'],
            'disposisi_kepada_free_email' => [''],
        ]);

        $this->assertDatabaseMissing('users', ['name' => 'Divisi Keuangan']);
        $disposisi = Disposisi::where('surat_masuk_id', $surat->id)->first();
        $this->assertContains('Divisi Keuangan', $disposisi->disposisi_kepada);
    }

    public function test_freetext_email_sudah_terdaftar_tidak_dibuat_duplikat(): void
    {
        $sekretaris = $this->user($this->sekretarisRole);
        $existing = $this->user($this->unitLainRole);
        $surat = $this->createSuratMasuk($sekretaris);

        $this->actingAs($sekretaris)->post('/disposisi', [
            'surat_masuk_id' => $surat->id,
            'jenis' => 'ke_pelaksana',
            'disposisi_kepada_free' => ['Nama Lain'],
            'disposisi_kepada_free_email' => [$existing->email],
        ]);

        $this->assertDatabaseCount('users', 2);
        $this->assertNull(User::where('email', $existing->email)->where('name', 'Nama Lain')->first());
    }
}
