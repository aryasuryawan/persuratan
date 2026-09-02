<?php

namespace Tests\Feature;

use App\Models\Klasifikasi;
use App\Models\NotulensiRapim;
use App\Models\Role;
use App\Models\StatusSurat;
use App\Models\SuratAkses;
use App\Models\SuratKeluar;
use App\Models\SuratKeputusan;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Services\DocumentSearchService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;
    private Role $unitLainRole;
    private Role $noPermRole;
    private Klasifikasi $restricted;
    private Klasifikasi $normal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['kode' => 'admin', 'label' => 'Administrator']);
        $this->unitLainRole = Role::create(['kode' => 'unit_lain', 'label' => 'Unit Lain']);
        $this->noPermRole = Role::create(['kode' => 'bebas', 'label' => 'Tanpa Izin']);

        $this->seed(PermissionSeeder::class);

        StatusSurat::create(['kode' => 'received', 'label' => 'Diterima', 'urutan' => 1]);

        $this->restricted = Klasifikasi::create([
            'kode' => 'TERBATAS',
            'nama' => 'Terbatas',
            'is_rahasia' => true,
        ]);

        $this->normal = Klasifikasi::create([
            'kode' => 'UMUM',
            'nama' => 'Umum',
            'is_rahasia' => false,
        ]);
    }

    private function user(Role $role): User
    {
        return User::factory()->create(['role_id' => $role->id]);
    }

    private function createSuratMasuk(User $creator, array $overrides = []): SuratMasuk
    {
        return SuratMasuk::create(array_merge([
            'nomor_agenda' => 'AG-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            'pengirim' => 'PT Mitra Sejahtera',
            'perihal' => 'Penawaran Kerja Sama Proyek',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $this->normal->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $creator->id,
        ], $overrides));
    }

    private function createSuratKeluar(string $nomor, string $perihal): SuratKeluar
    {
        return SuratKeluar::create([
            'nomor_surat' => $nomor,
            'perihal' => $perihal,
            'tujuan_surat' => 'Dinas Pendidikan',
            'tanggal_surat' => today(),
            'klasifikasi_id' => $this->normal->id,
            'pimpinan_pengirim_id' => $this->user($this->adminRole)->id,
            'created_by' => $this->user($this->adminRole)->id,
        ]);
    }

    private function service(): DocumentSearchService
    {
        return app(DocumentSearchService::class);
    }

    public function test_admin_dapat_mencari_surat_keluar(): void
    {
        $admin = $this->user($this->adminRole);
        $this->createSuratKeluar('SKL-2024-0012', 'Kerja Sama Pemerintah Daerah');

        $result = $this->service()->search($admin, 'surat_keluar', 'SKL-2024-0012');

        $this->assertSame(1, $result['found']);
        $this->assertSame('SKL-2024-0012', $result['results'][0]['label']);
        $this->assertSame(route('surat-keluar.show', SuratKeluar::first()->uuid), $result['results'][0]['url']);
    }

    public function test_user_tanpa_permission_modul_mendapat_hasil_kosong(): void
    {
        $user = $this->user($this->noPermRole);
        $this->createSuratKeluar('SKL-2024-0099', 'Kerja Sama ABC');

        $result = $this->service()->search($user, 'surat_keluar', 'SKL-2024-0099');

        $this->assertSame(0, $result['found']);
        $this->assertStringContainsString('tidak memiliki akses', $result['note']);
    }

    public function test_surat_masuk_restricted_tidak_muncul_tanpa_akses_manual(): void
    {
        $pimpinan = $this->user($this->adminRole);
        $unitLain = $this->user($this->unitLainRole);
        $this->createSuratMasuk($unitLain, [
            'klasifikasi_id' => $this->restricted->id,
            'perihal' => 'Dokumen Rahasia Terbatas',
        ]);

        $result = $this->service()->search($unitLain, 'surat_masuk', 'Rahasia');

        $this->assertSame(0, $result['found']);
    }

    public function test_surat_masuk_restricted_muncul_dengan_akses_manual(): void
    {
        $pimpinan = $this->user($this->adminRole);
        $unitLain = $this->user($this->unitLainRole);
        $surat = $this->createSuratMasuk($unitLain, [
            'klasifikasi_id' => $this->restricted->id,
            'perihal' => 'Dokumen Rahasia Terbatas',
        ]);
        SuratAkses::create([
            'aksesable_type' => SuratMasuk::class,
            'aksesable_id' => $surat->id,
            'user_id' => $unitLain->id,
            'level' => 'view',
            'diberikan_oleh_id' => $pimpinan->id,
        ]);

        $result = $this->service()->search($unitLain, 'surat_masuk', 'Rahasia');

        $this->assertSame(1, $result['found']);
        $this->assertSame(route('penerimaan-surat.show', $surat->uuid), $result['results'][0]['url']);
    }

    public function test_arsip_rahasia_tidak_muncul_untuk_user_tanpa_akses(): void
    {
        $unitLain = $this->user($this->unitLainRole);
        SuratKeputusan::create([
            'nomor_sk' => 'SK-777/2026',
            'perihal' => 'Pembentukan Komisi Rahasia',
            'kategori' => 'Organisasi',
            'tanggal_sk' => today(),
            'is_rahasia' => true,
            'dicatat_oleh_id' => $unitLain->id,
        ]);

        $result = $this->service()->search($unitLain, 'surat_keputusan', 'SK-777');

        $this->assertSame(0, $result['found']);
    }

    public function test_arsip_rahasia_muncul_untuk_admin(): void
    {
        $admin = $this->user($this->adminRole);
        $sk = SuratKeputusan::create([
            'nomor_sk' => 'SK-777/2026',
            'perihal' => 'Pembentukan Komisi Rahasia',
            'kategori' => 'Organisasi',
            'tanggal_sk' => today(),
            'is_rahasia' => true,
            'dicatat_oleh_id' => $admin->id,
        ]);

        $result = $this->service()->search($admin, 'surat_keputusan', 'Komisi Rahasia');

        $this->assertSame(1, $result['found']);
        $this->assertSame(route('surat-keputusan.show', $sk->uuid), $result['results'][0]['url']);
    }

    public function test_keyword_mencari_perihal_dan_hormati_limit(): void
    {
        $admin = $this->user($this->adminRole);
        foreach (range(1, 3) as $i) {
            $this->createSuratKeluar("SKL-2026-000{$i}", "Penawaran Kemitraan {$i}");
        }

        $result = $this->service()->search($admin, 'surat_keluar', 'Kemitraan', 2);

        $this->assertSame(2, $result['found']);
        $this->assertCount(2, $result['results']);
    }

    public function test_keyword_multi_kata_mencari_judul_tidak_berurutan(): void
    {
        $admin = $this->user($this->adminRole);
        $notulen = NotulensiRapim::create([
            'judul_rapat' => 'Rapat Pembagian Bonus Tebar Hewan Kurban 2026',
            'tanggal' => today(),
            'kategori' => 'Rutin',
            'klasifikasi_id' => $this->normal->id,
            'ringkasan_pembahasan' => 'Pembagian bonus dan hewan kurban.',
            'is_rahasia' => false,
            'dicatat_oleh_id' => $admin->id,
        ]);

        $result = $this->service()->search($admin, 'notulen_rapim', 'bonus kurban');

        $this->assertSame(1, $result['found']);
        $this->assertSame(route('notulensi-rapim.show', $notulen->uuid), $result['results'][0]['url']);
    }

    public function test_modul_all_mencari_seluruh_modul_sekaligus(): void
    {
        $admin = $this->user($this->adminRole);
        $notulen = NotulensiRapim::create([
            'judul_rapat' => 'Rapat Pembagian Bonus Tebar Hewan Kurban 2026',
            'tanggal' => today(),
            'kategori' => 'Rutin',
            'klasifikasi_id' => $this->normal->id,
            'ringkasan_pembahasan' => 'Pembagian bonus dan hewan kurban.',
            'is_rahasia' => false,
            'dicatat_oleh_id' => $admin->id,
        ]);
        $surat = $this->createSuratKeluar('SKL-2026-0202', 'Permohonan Bonus Tahunan Karyawan');

        $result = $this->service()->search($admin, 'all', 'bonus');

        $this->assertSame(2, $result['found']);
        $this->assertContains('notulen_rapim', array_column($result['results'], 'modul'));
        $this->assertContains('surat_keluar', array_column($result['results'], 'modul'));
        $this->assertSame(
            route('notulensi-rapim.show', $notulen->uuid),
            collect($result['results'])->firstWhere('modul', 'notulen_rapim')['url']
        );
        $this->assertSame(
            route('surat-keluar.show', $surat->uuid),
            collect($result['results'])->firstWhere('modul', 'surat_keluar')['url']
        );
    }

    public function test_modul_tidak_dikenal_kembali_kosong(): void
    {
        $admin = $this->user($this->adminRole);

        $result = $this->service()->search($admin, 'modul_aneh', 'apa');

        $this->assertSame(0, $result['found']);
        $this->assertEmpty($result['results']);
    }
}
