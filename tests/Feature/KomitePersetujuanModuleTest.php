<?php

namespace Tests\Feature;

use App\Models\KomitePersetujuan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KomitePersetujuanModuleTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $sekretarisRole;

    private Role $pimpinanRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['kode' => 'admin', 'label' => 'Administrator']);
        $this->sekretarisRole = Role::create(['kode' => 'sekretaris', 'label' => 'Sekretaris']);
        $this->pimpinanRole = Role::create(['kode' => 'pimpinan', 'label' => 'Pimpinan']);
    }

    private function userDenganRole(Role $role): User
    {
        return User::factory()->create(['role_id' => $role->id]);
    }

    private function payloadKomite(array $overrides = []): array
    {
        return array_merge([
            'judul' => 'Persetujuan Pembelian Aset IT',
            'jenis' => 'pembelian',
            'tanggal_rapat' => '2026-07-10',
            'daftar_peserta' => "Ahmad\nBudi",
            'nilai_pembelian' => '150000000',
            'hasil' => 'disetujui',
            'catatan_keputusan' => 'Disetujui dengan syarat RAB final.',
        ], $overrides);
    }

    public function test_sekretaris_dapat_membuat_dan_melihat_hasil_komite(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);

        $this->actingAs($sekretaris)
            ->post('/komite-persetujuan', $this->payloadKomite())
            ->assertRedirect(route('komite-persetujuan.show', KomitePersetujuan::first()->uuid));

        $this->assertDatabaseHas('komite_persetujuan', [
            'judul' => 'Persetujuan Pembelian Aset IT',
            'dicatat_oleh_id' => $sekretaris->id,
        ]);

        $komite = KomitePersetujuan::first();
        $this->actingAs($sekretaris)
            ->get(route('komite-persetujuan.show', $komite->uuid))
            ->assertOk()
            ->assertSee('Persetujuan Pembelian Aset IT');
    }

    public function test_admin_dapat_membuat_hasil_komite(): void
    {
        $admin = $this->userDenganRole($this->adminRole);

        $this->actingAs($admin)
            ->post('/komite-persetujuan', $this->payloadKomite(['judul' => 'Prinsip Proyek Baru']))
            ->assertRedirect();

        $this->assertDatabaseHas('komite_persetujuan', ['judul' => 'Prinsip Proyek Baru']);
    }

    public function test_pimpinan_hanya_bisa_melihat_tidak_bisa_mengelola(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $komite = KomitePersetujuan::create($this->payloadKomite(['dicatat_oleh_id' => $sekretaris->id]));

        $pimpinan = $this->userDenganRole($this->pimpinanRole);
        $this->actingAs($pimpinan);

        $this->get('/komite-persetujuan')->assertOk();
        $this->get(route('komite-persetujuan.show', $komite->uuid))->assertOk();
        $this->get('/komite-persetujuan/create')->assertForbidden();
        $this->post('/komite-persetujuan', $this->payloadKomite())->assertForbidden();
        $this->get(route('komite-persetujuan.edit', $komite->uuid))->assertForbidden();
        $this->put(route('komite-persetujuan.update', $komite->uuid), $this->payloadKomite())->assertForbidden();
        $this->delete(route('komite-persetujuan.destroy', $komite->uuid))->assertForbidden();
    }

    public function test_upload_foto_tersimpan_dan_terhubung_polimorfik(): void
    {
        Storage::fake('public');
        $sekretaris = $this->userDenganRole($this->sekretarisRole);

        $this->actingAs($sekretaris)
            ->post('/komite-persetujuan', [
                ...$this->payloadKomite(),
                'foto_kegiatan' => [
                    UploadedFile::fake()->image('rapat1.jpg', 400, 300),
                    UploadedFile::fake()->image('rapat2.png', 400, 300),
                ],
                'foto_caption' => 'Rapat komite pembelian',
            ])
            ->assertRedirect();

        $komite = KomitePersetujuan::first();
        $this->assertSame(2, $komite->fotoKegiatan()->count());

        $urutan = $komite->fotoKegiatan()->pluck('urutan')->all();
        $this->assertSame([0, 1], $urutan);

        foreach ($komite->fotoKegiatan as $foto) {
            $this->assertSame('Rapat komite pembelian', $foto->caption);
            Storage::disk('public')->assertExists($foto->path);
        }

        $this->assertDatabaseHas('foto_kegiatan', [
            'kegiatan_type' => KomitePersetujuan::class,
            'kegiatan_id' => $komite->id,
        ]);
    }

    public function test_hasil_dan_jenis_tidak_valid_ditolak(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);

        $this->actingAs($sekretaris)
            ->from('/komite-persetujuan/create')
            ->post('/komite-persetujuan', $this->payloadKomite([
                'hasil' => 'entah',
                'jenis' => 'sesuatu',
            ]))
            ->assertSessionHasErrors(['hasil', 'jenis']);

        $this->assertDatabaseCount('komite_persetujuan', 0);
    }

    public function test_sekretaris_dapat_menghapus_hasil_komite(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $komite = KomitePersetujuan::create($this->payloadKomite(['dicatat_oleh_id' => $sekretaris->id]));

        $this->actingAs($sekretaris)
            ->delete(route('komite-persetujuan.destroy', $komite->uuid))
            ->assertRedirect(route('komite-persetujuan.index'));

        $this->assertDatabaseMissing('komite_persetujuan', ['uuid' => $komite->uuid]);
    }
}
