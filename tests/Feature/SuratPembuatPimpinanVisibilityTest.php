<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\Klasifikasi;
use App\Models\Role;
use App\Models\SekretarisPimpinan;
use App\Models\StatusSurat;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratPembuatPimpinanVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;
    private Role $sekretarisRole;
    private Role $pimpinanRole;
    private Role $unitLainRole;
    private Klasifikasi $restrictedKlasifikasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['kode' => 'admin', 'label' => 'Administrator']);
        $this->sekretarisRole = Role::create(['kode' => 'sekretaris', 'label' => 'Sekretaris']);
        $this->pimpinanRole = Role::create(['kode' => 'pimpinan', 'label' => 'Pimpinan']);
        $this->unitLainRole = Role::create(['kode' => 'unit_lain', 'label' => 'Unit Lain']);

        $this->seed(PermissionSeeder::class);

        StatusSurat::create(['kode' => 'received', 'label' => 'Diterima', 'urutan' => 1]);
        StatusSurat::create(['kode' => 'verified', 'label' => 'Diverifikasi', 'urutan' => 2]);

        $this->restrictedKlasifikasi = Klasifikasi::create([
            'kode' => 'TERBATAS',
            'nama' => 'Terbatas',
            'is_rahasia' => true,
        ]);
    }

    private function createSuratMasuk(User $creator, array $overrides = []): SuratMasuk
    {
        return SuratMasuk::create(array_merge([
            'nomor_agenda' => 'AG-' . now()->format('Ymd') . '-' . StrRandom(),
            'pengirim' => 'PT Test',
            'perihal' => 'Surat Test',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $this->restrictedKlasifikasi->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $creator->id,
        ], $overrides));
    }

    private function createSuratKeluar(User $creator, User $pimpinan, array $overrides = []): SuratKeluar
    {
        return SuratKeluar::create(array_merge([
            'nomor_surat' => 'SK-' . StrRandom(),
            'perihal' => 'Surat Keluar Test',
            'tujuan_surat' => 'Tujuan',
            'tanggal_surat' => today(),
            'klasifikasi_id' => $this->restrictedKlasifikasi->id,
            'pimpinan_pengirim_id' => $pimpinan->id,
            'created_by' => $creator->id,
        ], $overrides));
    }

    private function proxySekretarisUntuk(User $pimpinan): User
    {
        $sekretaris = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        SekretarisPimpinan::create([
            'sekretaris_id' => $sekretaris->id,
            'pimpinan_id' => $pimpinan->id,
            'is_active' => true,
        ]);
        return $sekretaris;
    }

    private function forwardToPimpinan(SuratMasuk $surat, User $dari, User $pimpinan): Disposisi
    {
        return Disposisi::create([
            'surat_masuk_id' => $surat->id,
            'dari_user_id' => $dari->id,
            'jenis' => 'langsung_pimpinan',
            'atas_nama_pimpinan_id' => $pimpinan->id,
        ]);
    }

    public function test_admin_bisa_lihat_surat_flagged(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $admin = User::factory()->create(['role_id' => $this->adminRole->id]);
        $surat = $this->createSuratMasuk($admin, [
            'hanya_pembuat_pimpinan' => true,
            'tujuan_pimpinan_id' => $pimpinan->id,
        ]);

        $this->actingAs($admin);
        $this->get('/penerimaan-surat/' . $surat->uuid)->assertOk();
    }

    public function test_sekretaris_pembuat_bisa_lihat_surat_flagged(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $surat = $this->createSuratMasuk($sekretaris, [
            'hanya_pembuat_pimpinan' => true,
            'tujuan_pimpinan_id' => $pimpinan->id,
        ]);
        $this->forwardToPimpinan($surat, $sekretaris, $pimpinan);

        $this->actingAs($sekretaris);
        $this->get('/penerimaan-surat/' . $surat->uuid)->assertOk();
    }

    public function test_sekretaris_dengan_pimpinan_sama_bisa_lihat_surat_flagged(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $pembuat = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $sekretarisLain = $this->proxySekretarisUntuk($pimpinan);

        $surat = $this->createSuratMasuk($pembuat, [
            'hanya_pembuat_pimpinan' => true,
            'tujuan_pimpinan_id' => $pimpinan->id,
        ]);
        $this->forwardToPimpinan($surat, $pembuat, $pimpinan);

        $this->actingAs($sekretarisLain);
        $this->get('/penerimaan-surat/' . $surat->uuid)->assertOk();
    }

    public function test_sekretaris_beda_pimpinan_tidak_bisa_lihat_surat_flagged(): void
    {
        $pimpinanA = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $pimpinanB = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $pembuat = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $sekretarisLain = $this->proxySekretarisUntuk($pimpinanB);

        $surat = $this->createSuratMasuk($pembuat, [
            'hanya_pembuat_pimpinan' => true,
            'tujuan_pimpinan_id' => $pimpinanA->id,
        ]);
        $this->forwardToPimpinan($surat, $pembuat, $pimpinanA);

        $this->actingAs($sekretarisLain);
        $this->get('/penerimaan-surat/' . $surat->uuid)->assertForbidden();
    }

    public function test_pimpinan_tujuan_bisa_lihat_surat_flagged(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $surat = $this->createSuratMasuk($sekretaris, [
            'hanya_pembuat_pimpinan' => true,
            'tujuan_pimpinan_id' => $pimpinan->id,
        ]);
        $this->forwardToPimpinan($surat, $sekretaris, $pimpinan);

        $this->actingAs($pimpinan);
        $this->get('/penerimaan-surat/' . $surat->uuid)->assertOk();
    }

    public function test_unit_lain_tidak_bisa_lihat_surat_flagged(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $unitLain = User::factory()->create(['role_id' => $this->unitLainRole->id]);

        $surat = $this->createSuratMasuk($sekretaris, [
            'hanya_pembuat_pimpinan' => true,
            'tujuan_pimpinan_id' => $pimpinan->id,
        ]);
        $this->forwardToPimpinan($surat, $sekretaris, $pimpinan);

        $this->actingAs($unitLain);
        $this->get('/penerimaan-surat/' . $surat->uuid)->assertForbidden();
    }

    public function test_surat_flagged_tidak_muncul_di_listing_sekretaris_beda_pimpinan(): void
    {
        $pimpinanA = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $pimpinanB = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $pembuat = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $sekretarisLain = $this->proxySekretarisUntuk($pimpinanB);

        $surat = $this->createSuratMasuk($pembuat, [
            'hanya_pembuat_pimpinan' => true,
            'tujuan_pimpinan_id' => $pimpinanA->id,
        ]);
        $this->forwardToPimpinan($surat, $pembuat, $pimpinanA);

        $this->actingAs($sekretarisLain);
        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')->getJson('/api/surat-masuk');

        $response->assertJsonCount(0, 'data');
    }

    public function test_surat_flagged_muncul_di_listing_sekretaris_pembuat(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = User::factory()->create(['role_id' => $this->sekretarisRole->id]);

        $surat = $this->createSuratMasuk($sekretaris, [
            'hanya_pembuat_pimpinan' => true,
            'tujuan_pimpinan_id' => $pimpinan->id,
        ]);
        $this->forwardToPimpinan($surat, $sekretaris, $pimpinan);

        $this->actingAs($sekretaris);
        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')->getJson('/api/surat-masuk');

        $response->assertJsonCount(1, 'data');
    }

    public function test_surat_keluar_flagged_hanya_pimpinan_pengirim_bisa_lihat(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $unitLain = User::factory()->create(['role_id' => $this->unitLainRole->id]);

        $surat = $this->createSuratKeluar($sekretaris, $pimpinan, [
            'hanya_pembuat_pimpinan' => true,
        ]);

        $this->actingAs($pimpinan);
        $this->get('/surat-keluar/' . $surat->uuid)->assertOk();

        $this->actingAs($unitLain);
        $this->get('/surat-keluar/' . $surat->uuid)->assertForbidden();
    }

    public function test_surat_keluar_sekretaris_proxy_bisa_lihat_flagged(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = $this->proxySekretarisUntuk($pimpinan);

        $surat = $this->createSuratKeluar($sekretaris, $pimpinan, [
            'hanya_pembuat_pimpinan' => true,
        ]);

        $this->actingAs($sekretaris);
        $this->get('/surat-keluar/' . $surat->uuid)->assertOk();
    }

    public function test_surat_keluar_sekretaris_beda_pimpinan_tidak_bisa_lihat_flagged(): void
    {
        $pimpinanA = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $pimpinanB = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretarisLain = $this->proxySekretarisUntuk($pimpinanB);

        $surat = $this->createSuratKeluar(
            User::factory()->create(['role_id' => $this->sekretarisRole->id]),
            $pimpinanA,
            ['hanya_pembuat_pimpinan' => true]
        );

        $this->actingAs($sekretarisLain);
        $this->get('/surat-keluar/' . $surat->uuid)->assertForbidden();
    }

    public function test_surat_keluar_flagged_tidak_muncul_listing_unit_lain(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $unitLain = User::factory()->create(['role_id' => $this->unitLainRole->id]);

        $this->createSuratKeluar($sekretaris, $pimpinan, [
            'hanya_pembuat_pimpinan' => true,
        ]);

        $this->actingAs($unitLain);
        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')->getJson('/api/surat-keluar');

        $response->assertJsonCount(0, 'data');
    }

    public function test_statistik_surat_masuk_hitung_surat_flagged_untuk_sekretaris(): void
    {
        $pimpinanA = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $pimpinanB = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $pembuat = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $sekretarisLain = $this->proxySekretarisUntuk($pimpinanB);

        $this->createSuratMasuk($pembuat, [
            'hanya_pembuat_pimpinan' => true,
            'tujuan_pimpinan_id' => $pimpinanA->id,
        ]);

        $this->actingAs($sekretarisLain);
        $response = $this->get('/penerimaan-surat');

        $response->assertOk();
        $response->assertSee('Total Surat');
        $this->assertEquals(1, $response->viewData('total'));
    }

    public function test_statistik_surat_keluar_hitung_surat_flagged_untuk_unit_lain(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $unitLain = User::factory()->create(['role_id' => $this->unitLainRole->id]);

        $this->createSuratKeluar($sekretaris, $pimpinan, [
            'hanya_pembuat_pimpinan' => true,
        ]);

        $this->actingAs($unitLain);
        $response = $this->get('/surat-keluar');

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('total'));
    }

    public function test_dashboard_statistik_hitung_surat_flagged(): void
    {
        foreach (['forwarded_to_leadership', 'assigned', 'handled_by_leadership', 'in_progress', 'completed'] as $ukdi => $kode) {
            StatusSurat::create(['kode' => $kode, 'label' => ucwords(str_replace('_', ' ', $kode)), 'urutan' => $ukdi + 3]);
        }

        $pimpinanA = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $pimpinanB = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $pembuat = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $sekretarisLain = $this->proxySekretarisUntuk($pimpinanB);

        $this->createSuratMasuk($pembuat, [
            'hanya_pembuat_pimpinan' => true,
            'tujuan_pimpinan_id' => $pimpinanA->id,
        ]);

        $this->actingAs($sekretarisLain);
        $response = $this->get('/dashboard');

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('totalSuratMasuk'));
    }
}

function StrRandom(): string
{
    return substr(md5((string) mt_rand()), 0, 8);
}