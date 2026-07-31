<?php

namespace Tests\Feature;

use App\Models\NotulensiRapim;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotulensiRapimModuleTest extends TestCase
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

    private function payloadRapim(array $overrides = []): array
    {
        return array_merge([
            'judul_rapat' => 'Rapim Evaluasi Program Q3',
            'tanggal' => '2026-07-20',
            'daftar_peserta' => "Direktur\nKepala Divisi",
            'ringkasan_pembahasan' => 'Evaluasi capaian program dan kendala lapangan.',
            'keputusan_arahan' => 'Agar setiap divisi melaporkan progres mingguan.',
        ], $overrides);
    }

    public function test_sekretaris_dapat_membuat_dan_melihat_notulensi(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);

        $this->actingAs($sekretaris)
            ->post('/notulensi-rapim', $this->payloadRapim())
            ->assertRedirect(route('notulensi-rapim.show', NotulensiRapim::first()->uuid));

        $this->assertDatabaseHas('notulensi_rapim', [
            'judul_rapat' => 'Rapim Evaluasi Program Q3',
            'dicatat_oleh_id' => $sekretaris->id,
        ]);

        $rapim = NotulensiRapim::first();
        $this->actingAs($sekretaris)
            ->get(route('notulensi-rapim.show', $rapim->uuid))
            ->assertOk()
            ->assertSee('Rapim Evaluasi Program Q3');
    }

    public function test_admin_dapat_membuat_notulensi(): void
    {
        $admin = $this->userDenganRole($this->adminRole);

        $this->actingAs($admin)
            ->post('/notulensi-rapim', $this->payloadRapim(['judul_rapat' => 'Rapim Anggaran']))
            ->assertRedirect();

        $this->assertDatabaseHas('notulensi_rapim', ['judul_rapat' => 'Rapim Anggaran']);
    }

    public function test_pimpinan_hanya_bisa_melihat_tidak_bisa_mengelola(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);
        $rapim = NotulensiRapim::create($this->payloadRapim(['dicatat_oleh_id' => $sekretaris->id]));

        $pimpinan = $this->userDenganRole($this->pimpinanRole);
        $this->actingAs($pimpinan);

        $this->get('/notulensi-rapim')->assertOk();
        $this->get(route('notulensi-rapim.show', $rapim->uuid))->assertOk();
        $this->get('/notulensi-rapim/create')->assertForbidden();
        $this->post('/notulensi-rapim', $this->payloadRapim())->assertForbidden();
        $this->get(route('notulensi-rapim.edit', $rapim->uuid))->assertForbidden();
        $this->put(route('notulensi-rapim.update', $rapim->uuid), $this->payloadRapim())->assertForbidden();
        $this->delete(route('notulensi-rapim.destroy', $rapim->uuid))->assertForbidden();
    }

    public function test_lampiran_dan_foto_tersimpan_terhubung_polimorfik(): void
    {
        Storage::fake('public');
        $sekretaris = $this->userDenganRole($this->sekretarisRole);

        $this->actingAs($sekretaris)
            ->post('/notulensi-rapim', [
                ...$this->payloadRapim(),
                'lampiran' => [
                    UploadedFile::fake()->create('daftar-hadir.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
                ],
                'lampiran_keterangan' => ['Daftar hadir'],
                'foto_kegiatan' => [UploadedFile::fake()->image('rapim.jpg', 400, 300)],
            ])
            ->assertRedirect();

        $rapim = NotulensiRapim::first();

        $this->assertSame(1, $rapim->lampiran()->count());
        $this->assertSame(1, $rapim->fotoKegiatan()->count());

        $lampiran = $rapim->lampiran()->first();
        $this->assertSame('daftar-hadir.docx', $lampiran->nama_file);
        Storage::disk('public')->assertExists($lampiran->path);

        $this->assertDatabaseHas('lampiran', [
            'lampirkan_type' => NotulensiRapim::class,
            'lampirkan_id' => $rapim->id,
        ]);
        $this->assertDatabaseHas('foto_kegiatan', [
            'kegiatan_type' => NotulensiRapim::class,
            'kegiatan_id' => $rapim->id,
        ]);
    }

    public function test_ringkasan_pembahasan_wajib_diisi(): void
    {
        $sekretaris = $this->userDenganRole($this->sekretarisRole);

        $this->actingAs($sekretaris)
            ->from('/notulensi-rapim/create')
            ->post('/notulensi-rapim', $this->payloadRapim(['ringkasan_pembahasan' => '']))
            ->assertSessionHasErrors('ringkasan_pembahasan');

        $this->assertDatabaseCount('notulensi_rapim', 0);
    }
}
