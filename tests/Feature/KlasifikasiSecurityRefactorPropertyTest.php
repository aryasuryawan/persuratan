<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Klasifikasi;
use App\Models\Topik;
use App\Models\SuratMasuk;
use App\Models\StatusSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class KlasifikasiSecurityRefactorPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Property 1: Klasifikasi contains exactly 3 security levels
     * Validated: Requirements 1.1
     */
    public function test_klasifikasi_contains_exactly_3_security_levels(): void
    {
        $klasifikasi = Klasifikasi::all();

        $this->assertCount(3, $klasifikasi);
        $this->assertTrue($klasifikasi->contains('kode', 'UMUM'));
        $this->assertTrue($klasifikasi->contains('kode', 'TERBATAS'));
        $this->assertTrue($klasifikasi->contains('kode', 'RAHASIA'));
    }

    /**
     * Property 2: Is-rahasia derives from klasifikasi relationship
     * Validated: Requirements 1.2
     */
    public function test_is_rahasia_accessor_derives_from_klasifikasi(): void
    {
        $umum = Klasifikasi::where('kode', 'UMUM')->first();
        $terbatas = Klasifikasi::where('kode', 'TERBATAS')->first();
        $rahasia = Klasifikasi::where('kode', 'RAHASIA')->first();

        $role = \App\Models\Role::firstOrCreate(['kode' => 'admin'], ['label' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        StatusSurat::firstOrCreate(['kode' => 'received'], ['label' => 'Diterima', 'urutan' => 1]);

        $docUmum = SuratMasuk::create([
            'nomor_agenda' => 'AG-001',
            'pengirim' => 'Test',
            'perihal' => 'Test',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $umum->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $user->id,
        ]);
        $this->assertFalse($docUmum->is_rahasia);

        $docTerbatas = SuratMasuk::create([
            'nomor_agenda' => 'AG-002',
            'pengirim' => 'Test',
            'perihal' => 'Test',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $terbatas->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $user->id,
        ]);
        $this->assertTrue($docTerbatas->is_rahasia);

        $docRahasia = SuratMasuk::create([
            'nomor_agenda' => 'AG-003',
            'pengirim' => 'Test',
            'perihal' => 'Test',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $rahasia->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $user->id,
        ]);
        $this->assertTrue($docRahasia->is_rahasia);
    }

    /**
     * Property 3: No is_rahasia column on document tables
     * Validated: Requirements 1.3
     */
    public function test_is_rahasia_column_not_exists_on_document_tables(): void
    {
        $this->assertFalse(Schema::hasColumn('surat_masuk', 'is_rahasia'));
        $this->assertFalse(Schema::hasColumn('surat_keluar', 'is_rahasia'));
    }

    /**
     * Property 4: Topik multi-select stores correctly
     * Validated: Requirements 2.2
     */
    public function test_topik_sync_stores_correctly(): void
    {
        $topiks = Topik::all();
        $topikIds = $topiks->pluck('id')->toArray();

        $role = \App\Models\Role::firstOrCreate(['kode' => 'admin'], ['label' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        StatusSurat::firstOrCreate(['kode' => 'received'], ['label' => 'Diterima', 'urutan' => 1]);
        $umum = Klasifikasi::where('kode', 'UMUM')->first();

        $surat = SuratMasuk::create([
            'nomor_agenda' => 'AG-004',
            'pengirim' => 'Test',
            'perihal' => 'Test',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $umum->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $user->id,
        ]);
        $surat->topiks()->sync($topikIds);

        $this->assertCount(4, $surat->topiks);
        $this->assertEqualsCanonicalizing($topikIds, $surat->topiks->pluck('id')->toArray());
    }

    /**
     * Property 6: Topik filtering returns correct subset
     * Validated: Requirements 2.4
     */
    public function test_topik_filter_returns_correct_subset(): void
    {
        $topikKeuangan = Topik::where('kode', 'KEUANGAN')->first();
        $topikSDM = Topik::where('kode', 'SDM')->first();

        $role = \App\Models\Role::firstOrCreate(['kode' => 'admin'], ['label' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        StatusSurat::firstOrCreate(['kode' => 'received'], ['label' => 'Diterima', 'urutan' => 1]);
        $umum = Klasifikasi::where('kode', 'UMUM')->first();

        $docWithKeuangan = SuratMasuk::create([
            'nomor_agenda' => 'AG-005',
            'pengirim' => 'Test',
            'perihal' => 'Test',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $umum->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $user->id,
        ]);
        $docWithKeuangan->topiks()->attach($topikKeuangan);

        $docWithSDM = SuratMasuk::create([
            'nomor_agenda' => 'AG-006',
            'pengirim' => 'Test',
            'perihal' => 'Test',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $umum->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $user->id,
        ]);
        $docWithSDM->topiks()->attach($topikSDM);

        $filtered = SuratMasuk::whereHas('topiks', fn($q) => $q->where('topik.id', $topikKeuangan->id))->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals($docWithKeuangan->id, $filtered->first()->id);
    }

    /**
     * Property 8: Topik is optional on all forms
     * Validated: Requirements 3.4
     */
    public function test_topik_is_optional_on_document_creation(): void
    {
        $role = \App\Models\Role::firstOrCreate(['kode' => 'admin'], ['label' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        StatusSurat::firstOrCreate(['kode' => 'received'], ['label' => 'Diterima', 'urutan' => 1]);
        $umum = Klasifikasi::where('kode', 'UMUM')->first();

        $surat = SuratMasuk::create([
            'nomor_agenda' => 'AG-007',
            'pengirim' => 'Test',
            'perihal' => 'Test',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $umum->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $user->id,
        ]);

        $this->assertNull($surat->topiks()->first());
    }
}
