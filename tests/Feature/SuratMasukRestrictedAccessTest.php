<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\Klasifikasi;
use App\Models\Role;
use App\Models\SekretarisPimpinan;
use App\Models\StatusSurat;
use App\Models\SuratAkses;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratMasukRestrictedAccessTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;
    private Role $sekretarisRole;
    private Role $pimpinanRole;
    private Role $unitLainRole;
    private Role $ccSecurityRole;
    private Klasifikasi $restrictedKlasifikasi;
    private Klasifikasi $normalKlasifikasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['kode' => 'admin', 'label' => 'Administrator']);
        $this->sekretarisRole = Role::create(['kode' => 'sekretaris', 'label' => 'Sekretaris']);
        $this->pimpinanRole = Role::create(['kode' => 'pimpinan', 'label' => 'Pimpinan']);
        $this->unitLainRole = Role::create(['kode' => 'unit_lain', 'label' => 'Unit Lain']);
        $this->ccSecurityRole = Role::create(['kode' => 'cc_security', 'label' => 'CC Security']);

        StatusSurat::create(['kode' => 'received', 'label' => 'Diterima', 'urutan' => 1]);
        StatusSurat::create(['kode' => 'verified', 'label' => 'Diverifikasi', 'urutan' => 2]);

        $this->restrictedKlasifikasi = Klasifikasi::create([
            'kode' => 'restricted',
            'nama' => 'Restricted',
            'is_rahasia' => true,
        ]);

        $this->normalKlasifikasi = Klasifikasi::create([
            'kode' => 'normal',
            'nama' => 'Normal',
            'is_rahasia' => false,
        ]);
    }

    private function createSurat(User $creator, array $overrides = []): SuratMasuk
    {
        return SuratMasuk::create(array_merge([
            'nomor_agenda' => 'AG-' . now()->format('Ymd') . '-0001',
            'pengirim' => 'PT Test',
            'perihal' => 'Surat Test',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $this->restrictedKlasifikasi->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $creator->id,
        ], $overrides));
    }

    private function forwardToPimpinan(SuratMasuk $surat, User $dariUser, User $pimpinan): Disposisi
    {
        return Disposisi::create([
            'surat_masuk_id' => $surat->id,
            'dari_user_id' => $dariUser->id,
            'kepada_user_id' => null,
            'jenis' => 'langsung_pimpinan',
            'atas_nama_pimpinan_id' => $pimpinan->id,
        ]);
    }

    private function createSekretarisProxyingTo(User $pimpinan): User
    {
        $sekretaris = User::factory()->create([
            'role_id' => $this->sekretarisRole->id,
        ]);
        SekretarisPimpinan::create([
            'sekretaris_id' => $sekretaris->id,
            'pimpinan_id' => $pimpinan->id,
            'is_active' => true,
        ]);
        return $sekretaris;
    }

    public function test_sekretaris_tidak_proxy_tidak_bisa_lihat_restricted(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = User::factory()->create(['role_id' => $this->sekretarisRole->id]);
        $surat = $this->createSurat($sekretaris);
        $this->forwardToPimpinan($surat, $sekretaris, $pimpinan);

        $this->actingAs($sekretaris);
        $response = $this->get('/penerimaan-surat/' . $surat->uuid);

        $response->assertForbidden();
    }

    public function test_sekretaris_proxy_bisa_lihat_restricted(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = $this->createSekretarisProxyingTo($pimpinan);
        $surat = $this->createSurat($sekretaris);
        $this->forwardToPimpinan($surat, $sekretaris, $pimpinan);

        $this->actingAs($sekretaris);
        $response = $this->get('/penerimaan-surat/' . $surat->uuid);

        $response->assertOk();
    }

    public function test_pimpinan_penerima_bisa_lihat_restricted(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $surat = $this->createSurat($pimpinan);
        $this->forwardToPimpinan($surat, $pimpinan, $pimpinan);

        $this->actingAs($pimpinan);
        $response = $this->get('/penerimaan-surat/' . $surat->uuid);

        $response->assertOk();
    }

    public function test_pimpinan_lain_tidak_terkait_tidak_bisa_lihat_restricted(): void
    {
        $pimpinanPenerima = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $pimpinanLain = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $surat = $this->createSurat($pimpinanPenerima);
        $this->forwardToPimpinan($surat, $pimpinanPenerima, $pimpinanPenerima);

        $this->actingAs($pimpinanLain);
        $response = $this->get('/penerimaan-surat/' . $surat->uuid);

        $response->assertForbidden();
    }

    public function test_user_dengan_akses_manual_bisa_lihat_restricted(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $userLain = User::factory()->create(['role_id' => $this->unitLainRole->id]);
        $surat = $this->createSurat($userLain);
        $this->forwardToPimpinan($surat, $userLain, $pimpinan);
        SuratAkses::create([
            'aksesable_type' => SuratMasuk::class,
            'aksesable_id' => $surat->id,
            'user_id' => $userLain->id,
            'level' => 'view',
            'diberikan_oleh_id' => $pimpinan->id,
        ]);

        $this->actingAs($userLain);
        $response = $this->get('/penerimaan-surat/' . $surat->uuid);

        $response->assertOk();
    }

    public function test_cc_security_tidak_pernah_bisa_lihat_restricted(): void
    {
        $ccSecurity = User::factory()->create(['role_id' => $this->ccSecurityRole->id]);
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $surat = $this->createSurat($ccSecurity);
        $this->forwardToPimpinan($surat, $ccSecurity, $pimpinan);

        $this->actingAs($ccSecurity);
        $response = $this->get('/penerimaan-surat/' . $surat->uuid);

        $response->assertForbidden();
    }

    public function test_restricted_surat_tidak_muncul_listing_for_unauthorized_user(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $unitLain = User::factory()->create(['role_id' => $this->unitLainRole->id]);
        $surat = $this->createSurat($unitLain);
        $this->forwardToPimpinan($surat, $unitLain, $pimpinan);

        $this->actingAs($unitLain);
        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')->getJson('/api/surat-masuk');

        $data = $response->json('data');
        $this->assertEmpty($data);
    }

    public function test_sekretaris_proxy_bisa_lihat_restricted_di_listing(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = $this->createSekretarisProxyingTo($pimpinan);
        $surat = $this->createSurat($sekretaris);
        $this->forwardToPimpinan($surat, $sekretaris, $pimpinan);

        $this->actingAs($sekretaris);
        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')->getJson('/api/surat-masuk');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_user_dengan_akses_manual_lihat_restricted_di_listing(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $unitLain = User::factory()->create(['role_id' => $this->unitLainRole->id]);
        $surat = $this->createSurat($unitLain);
        $this->forwardToPimpinan($surat, $unitLain, $pimpinan);

        $userBaru = User::factory()->create(['role_id' => $this->unitLainRole->id]);
        $surat->aksesManual()->create([
            'user_id' => $userBaru->id,
            'level' => 'view',
            'diberikan_oleh_id' => $pimpinan->id,
        ]);

        $this->actingAs($userBaru);
        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')->getJson('/api/surat-masuk');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_hanya_sekretaris_proxy_dan_pimpinan_penerima_bisa_assign_akses(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = $this->createSekretarisProxyingTo($pimpinan);
        $unitLain = User::factory()->create(['role_id' => $this->unitLainRole->id]);
        $surat = $this->createSurat($unitLain);
        $this->forwardToPimpinan($surat, $unitLain, $pimpinan);

        $userBaru = User::factory()->create(['role_id' => $this->unitLainRole->id]);

        $this->actingAs($unitLain);
        $response = $this->postJson('/api/surat-masuk/' . $surat->uuid . '/akses', [
            'user_id' => $userBaru->id,
        ]);
        $response->assertForbidden();

        $this->actingAs($sekretaris);
        $response = $this->postJson('/api/surat-masuk/' . $surat->uuid . '/akses', [
            'user_id' => $userBaru->id,
        ]);
        $response->assertOk();

        $this->actingAs($pimpinan);
        $response = $this->postJson('/api/surat-masuk/' . $surat->uuid . '/akses', [
            'user_id' => $userBaru->id,
        ]);
        $response->assertOk();
    }

    public function test_cc_security_tidak_bisa_assign_akses_restricted(): void
    {
        $ccSecurity = User::factory()->create(['role_id' => $this->ccSecurityRole->id]);
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $surat = $this->createSurat($ccSecurity);
        $this->forwardToPimpinan($surat, $ccSecurity, $pimpinan);

        $userBaru = User::factory()->create(['role_id' => $this->unitLainRole->id]);

        $this->actingAs($ccSecurity);
        $response = $this->postJson('/api/surat-masuk/' . $surat->uuid . '/akses', [
            'user_id' => $userBaru->id,
        ]);

        $response->assertForbidden();
    }

    public function test_remove_akses_hanya_sekretaris_proxy_dan_pimpinan_penerima(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $sekretaris = $this->createSekretarisProxyingTo($pimpinan);
        $surat = $this->createSurat($pimpinan);
        $this->forwardToPimpinan($surat, $pimpinan, $pimpinan);

        $userDiberiAkses = User::factory()->create(['role_id' => $this->unitLainRole->id]);
        SuratAkses::create([
            'surat_id' => $surat->id,
            'user_id' => $userDiberiAkses->id,
            'level' => 'view',
            'diberikan_oleh_id' => $pimpinan->id,
        ]);

        $this->actingAs($userDiberiAkses);
        $response = $this->deleteJson('/api/surat-masuk/' . $surat->uuid . '/akses/' . $userDiberiAkses->id);
        $response->assertForbidden();

        $this->actingAs($sekretaris);
        $response = $this->deleteJson('/api/surat-masuk/' . $surat->uuid . '/akses/' . $userDiberiAkses->id);
        $response->assertOk();
    }

    public function test_cc_security_tidak_bisa_remove_akses_restricted(): void
    {
        $ccSecurity = User::factory()->create(['role_id' => $this->ccSecurityRole->id]);
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $surat = $this->createSurat($pimpinan);
        $this->forwardToPimpinan($surat, $pimpinan, $pimpinan);

        $userDiberiAkses = User::factory()->create(['role_id' => $this->unitLainRole->id]);
        SuratAkses::create([
            'surat_id' => $surat->id,
            'user_id' => $userDiberiAkses->id,
            'level' => 'view',
            'diberikan_oleh_id' => $pimpinan->id,
        ]);

        $this->actingAs($ccSecurity);
        $response = $this->deleteJson('/api/surat-masuk/' . $surat->uuid . '/akses/' . $userDiberiAkses->id);
        $response->assertForbidden();
    }

    public function test_pimpinan_penerima_bisa_remove_akses_restricted(): void
    {
        $pimpinan = User::factory()->create(['role_id' => $this->pimpinanRole->id]);
        $surat = $this->createSurat($pimpinan);
        $this->forwardToPimpinan($surat, $pimpinan, $pimpinan);

        $userDiberiAkses = User::factory()->create(['role_id' => $this->unitLainRole->id]);
        SuratAkses::create([
            'surat_id' => $surat->id,
            'user_id' => $userDiberiAkses->id,
            'level' => 'view',
            'diberikan_oleh_id' => $pimpinan->id,
        ]);

        $this->actingAs($pimpinan);
        $response = $this->deleteJson('/api/surat-masuk/' . $surat->uuid . '/akses/' . $userDiberiAkses->id);
        $response->assertOk();
    }
}