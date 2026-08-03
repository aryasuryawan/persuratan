<?php

namespace Tests\Feature;

use App\Models\Lampiran;
use App\Models\Role;
use App\Models\SuratKeputusan;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuratKeputusanModuleTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $sekretarisRole;

    private Role $pimpinanRole;

    private Role $unitLainRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['kode' => 'admin', 'label' => 'Administrator']);
        $this->sekretarisRole = Role::create(['kode' => 'sekretaris', 'label' => 'Sekretaris']);
        $this->pimpinanRole = Role::create(['kode' => 'pimpinan', 'label' => 'Pimpinan']);
        $this->unitLainRole = Role::create(['kode' => 'unit_lain', 'label' => 'Unit Lain']);

        $this->seed(PermissionSeeder::class);
    }

    private function userDenganRole(Role $role): User
    {
        return User::factory()->create(['role_id' => $role->id]);
    }

    private function payloadSk(array $overrides = []): array
    {
        return array_merge([
            'nomor_sk' => 'SK-001/2026',
            'perihal' => 'Pembentukan Panitia Kegiatan',
            'kategori' => 'Organisasi',
            'tanggal_sk' => '2026-07-01',
            'tanggal_berlaku' => '2026-07-15',
            'ditetapkan_oleh' => 'Direktur Eksekutif',
            'ringkasan' => 'Ringkasan isi SK.',
            'status' => 'aktif',
        ], $overrides);
    }

    public function test_sekretaris_dapat_membuat_dan_melihat_sk(): void
    {
        Storage::fake('local');
        $sekretaris = $this->userDenganRole($this->sekretarisRole);

        $this->actingAs($sekretaris)
            ->post('/surat-keputusan', $this->payloadSk())
            ->assertRedirect(route('surat-keputusan.show', SuratKeputusan::first()->uuid));

        $this->assertDatabaseHas('surat_keputusan', [
            'nomor_sk' => 'SK-001/2026',
            'dicatat_oleh_id' => $sekretaris->id,
        ]);

        $sk = SuratKeputusan::first();
        $this->actingAs($sekretaris)
            ->get(route('surat-keputusan.show', $sk->uuid))
            ->assertOk()
            ->assertSee('SK-001/2026');
    }

    public function test_admin_dapat_membuat_sk(): void
    {
        $admin = $this->userDenganRole($this->adminRole);

        $this->actingAs($admin)
            ->post('/surat-keputusan', $this->payloadSk(['nomor_sk' => 'SK-002/2026']))
            ->assertRedirect();

        $this->assertDatabaseHas('surat_keputusan', ['nomor_sk' => 'SK-002/2026']);
    }

    public function test_pimpinan_hanya_bisa_melihat_tidak_bisa_mengelola(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $sk = SuratKeputusan::create($this->payloadSk(['dicatat_oleh_id' => $sekretaris->id]));

        $pimpinan = $this->userDenganRole($this->pimpinanRole);
        $this->actingAs($pimpinan);

        $this->get('/surat-keputusan')->assertOk();
        $this->get(route('surat-keputusan.show', $sk->uuid))->assertOk();
        $this->get('/surat-keputusan/create')->assertForbidden();
        $this->post('/surat-keputusan', $this->payloadSk(['nomor_sk' => 'SK-999/2026']))->assertForbidden();
        $this->get(route('surat-keputusan.edit', $sk->uuid))->assertForbidden();
        $this->put(route('surat-keputusan.update', $sk->uuid), $this->payloadSk())->assertForbidden();
        $this->delete(route('surat-keputusan.destroy', $sk->uuid))->assertForbidden();

        $this->assertDatabaseMissing('surat_keputusan', ['nomor_sk' => 'SK-999/2026']);
    }

    public function test_unit_lain_juga_read_only(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $sk = SuratKeputusan::create($this->payloadSk(['dicatat_oleh_id' => $sekretaris->id]));

        $user = $this->userDenganRole($this->unitLainRole);
        $this->actingAs($user);

        $this->get(route('surat-keputusan.show', $sk->uuid))->assertOk();
        $this->post('/surat-keputusan', $this->payloadSk(['nomor_sk' => 'SK-888/2026']))->assertForbidden();
        $this->delete(route('surat-keputusan.destroy', $sk->uuid))->assertForbidden();
    }

    public function test_lampiran_dan_foto_tersimpan_dan_terhubung_polimorfik(): void
    {
        Storage::fake('local');
        $sekretaris = $this->userDenganRole($this->sekretarisRole);

        $response = $this->actingAs($sekretaris)->post('/surat-keputusan', [
            ...$this->payloadSk(),
            'lampiran' => [UploadedFile::fake()->create('sk-asli.pdf', 100, 'application/pdf')],
            'lampiran_keterangan' => ['SK Asli (scan)'],
            'foto_kegiatan' => [UploadedFile::fake()->image('kegiatan.jpg', 400, 300)],
            'foto_caption' => 'Rapat pembahasan SK',
        ]);

        $response->assertRedirect();
        $sk = SuratKeputusan::first();

        $this->assertSame(1, $sk->lampiran()->count());
        $this->assertSame(1, $sk->fotoKegiatan()->count());

        $lampiran = $sk->lampiran()->first();
        $this->assertSame('sk-asli.pdf', $lampiran->nama_file);
        $this->assertSame('pdf', $lampiran->tipe_file);
        $this->assertSame('SK Asli (scan)', $lampiran->keterangan);
        $this->assertGreaterThan(0, $lampiran->ukuran_bytes);
        $this->assertSame($sekretaris->id, $lampiran->diunggah_oleh_id);
        Storage::disk('local')->assertExists($lampiran->path);

        $foto = $sk->fotoKegiatan()->first();
        $this->assertSame('Rapat pembahasan SK', $foto->caption);
        $this->assertSame($sekretaris->id, $foto->diunggah_oleh_id);
        Storage::disk('local')->assertExists($foto->path);

        $this->assertDatabaseHas('lampiran', [
            'lampirkan_type' => SuratKeputusan::class,
            'lampirkan_id' => $sk->id,
        ]);
        $this->assertDatabaseHas('foto_kegiatan', [
            'kegiatan_type' => SuratKeputusan::class,
            'kegiatan_id' => $sk->id,
        ]);
    }

    public function test_update_dapat_mengganti_metadata_dan_menghapus_lampiran(): void
    {
        Storage::fake('local');
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $sk = SuratKeputusan::create($this->payloadSk(['dicatat_oleh_id' => $sekretaris->id]));

        $lampiran = Lampiran::create([
            'lampirkan_type' => SuratKeputusan::class,
            'lampirkan_id' => $sk->id,
            'nama_file' => 'lama.pdf',
            'path' => 'surat_keputusan/lampiran/lama.pdf',
            'tipe_file' => 'pdf',
            'diunggah_oleh_id' => $sekretaris->id,
        ]);

        $this->actingAs($sekretaris)
            ->put(route('surat-keputusan.update', $sk->uuid), [
                ...$this->payloadSk(['perihal' => 'Perihal Baru']),
                'hapus_lampiran' => [$lampiran->id],
            ])
            ->assertRedirect(route('surat-keputusan.show', $sk->uuid));

        $this->assertDatabaseHas('surat_keputusan', [
            'uuid' => $sk->uuid,
            'perihal' => 'Perihal Baru',
        ]);
        $this->assertDatabaseMissing('lampiran', ['id' => $lampiran->id]);
    }

    public function test_sekretaris_dapat_menghapus_sk_beserta_media(): void
    {
        Storage::fake('local');
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $sk = SuratKeputusan::create($this->payloadSk(['dicatat_oleh_id' => $sekretaris->id]));

        $path = 'surat_keputusan/lampiran/hapus.pdf';
        Storage::disk('local')->put($path, 'konten');
        $sk->lampiran()->create([
            'nama_file' => 'hapus.pdf',
            'path' => $path,
            'tipe_file' => 'pdf',
            'diunggah_oleh_id' => $sekretaris->id,
        ]);

        $this->actingAs($sekretaris)
            ->delete(route('surat-keputusan.destroy', $sk->uuid))
            ->assertRedirect(route('surat-keputusan.index'));

        $this->assertDatabaseMissing('surat_keputusan', ['uuid' => $sk->uuid]);
        $this->assertDatabaseMissing('lampiran', ['lampirkan_id' => $sk->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_media_diunduh_lewat_route_terotorisasi_bukan_file_public(): void
    {
        Storage::fake('local');
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $sk = SuratKeputusan::create($this->payloadSk(['dicatat_oleh_id' => $sekretaris->id]));

        $lampiran = Lampiran::create([
            'lampirkan_type' => SuratKeputusan::class,
            'lampirkan_id' => $sk->id,
            'nama_file' => 'rahasia.pdf',
            'path' => 'surat_keputusan/lampiran/rahasia.pdf',
            'tipe_file' => 'pdf',
            'diunggah_oleh_id' => $sekretaris->id,
        ]);
        Storage::disk('local')->put($lampiran->path, 'konten rahasia');

        $user = $this->userDenganRole($this->unitLainRole);
        $this->actingAs($user)
            ->get(route('arsip-media.lampiran.download', $lampiran))
            ->assertOk()
            ->assertDownload('rahasia.pdf');

        Storage::disk('public')->assertMissing($lampiran->path);

        $this->actingAs($user)
            ->get('/storage/'.$lampiran->path)
            ->assertNotFound();
    }

    public function test_arsip_rahasia_tidak_dapat_dilihat_unit_lain_tanpa_akses_manual(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $sk = SuratKeputusan::create($this->payloadSk([
            'dicatat_oleh_id' => $sekretaris->id,
            'is_rahasia' => true,
        ]));

        $unitLain = $this->userDenganRole($this->unitLainRole);

        $this->actingAs($unitLain)
            ->get(route('surat-keputusan.show', $sk->uuid))
            ->assertForbidden();
    }

    public function test_unit_lain_dengan_akses_manual_bisa_lihat_arsip_rahasia(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $sk = SuratKeputusan::create($this->payloadSk([
            'dicatat_oleh_id' => $sekretaris->id,
            'is_rahasia' => true,
        ]));

        $unitLain = $this->userDenganRole($this->unitLainRole);

        $this->actingAs($sekretaris)
            ->postJson('/arsip/surat-keputusan/' . $sk->uuid . '/akses', [
                'user_id' => $unitLain->id,
            ])
            ->assertOk();

        $this->actingAs($unitLain)
            ->get(route('surat-keputusan.show', $sk->uuid))
            ->assertOk();
    }

    public function test_pimpinan_dan_unit_lain_tidak_bisa_beri_akses_arsip(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $sk = SuratKeputusan::create($this->payloadSk([
            'dicatat_oleh_id' => $sekretaris->id,
            'is_rahasia' => true,
        ]));

        $pimpinan = $this->userDenganRole($this->pimpinanRole);
        $unitLain = $this->userDenganRole($this->unitLainRole);
        $userBaru = User::factory()->create(['role_id' => $this->unitLainRole->id]);

        $this->actingAs($pimpinan)
            ->postJson('/arsip/surat-keputusan/' . $sk->uuid . '/akses', [
                'user_id' => $userBaru->id,
            ])
            ->assertForbidden();

        $this->actingAs($unitLain)
            ->postJson('/arsip/surat-keputusan/' . $sk->uuid . '/akses', [
                'user_id' => $userBaru->id,
            ])
            ->assertForbidden();
    }

    public function test_update_mencatat_riwayat_perubahan_data(): void
    {
        Storage::fake('local');
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $sk = SuratKeputusan::create($this->payloadSk(['dicatat_oleh_id' => $sekretaris->id]));

        $this->actingAs($sekretaris)
            ->put(route('surat-keputusan.update', $sk->uuid), $this->payloadSk([
                'perihal' => 'Perihal Diperbarui',
                'status' => 'tidak_berlaku',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('riwayat_arsip', [
            'arsipable_type' => SuratKeputusan::class,
            'arsipable_id' => $sk->id,
            'field' => 'perihal',
        ]);

        $this->assertSame(1, $sk->fresh()->riwayatArsip()->where('field', 'perihal')->count());
    }
}
