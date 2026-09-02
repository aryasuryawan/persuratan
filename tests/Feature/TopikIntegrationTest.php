<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Klasifikasi;
use App\Models\Topik;
use App\Models\SuratMasuk;
use App\Models\StatusSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TopikIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_topik_crud_operations(): void
    {
        // Create
        $topik = Topik::create([
            'kode' => 'TEST',
            'nama' => 'Test Topik',
            'deskripsi' => 'Deskripsi test',
            'color' => 'primary',
        ]);

        $this->assertDatabaseHas('topik', ['kode' => 'TEST']);
        $this->assertEquals('Test Topik', $topik->nama);

        // Read
        $found = Topik::where('kode', 'TEST')->first();
        $this->assertNotNull($found);
        $this->assertEquals($topik->id, $found->id);

        // Update
        $found->update(['nama' => 'Updated Topik']);
        $this->assertEquals('Updated Topik', $found->fresh()->nama);

        // Delete
        $found->delete();
        $this->assertDatabaseMissing('topik', ['kode' => 'TEST']);
    }

    public function test_topik_attach_detach_to_surat_masuk(): void
    {
        $role = \App\Models\Role::firstOrCreate(['kode' => 'admin'], ['label' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        StatusSurat::firstOrCreate(['kode' => 'received'], ['label' => 'Diterima', 'urutan' => 1]);
        $umum = Klasifikasi::where('kode', 'UMUM')->first();

        $topikKeuangan = Topik::where('kode', 'KEUANGAN')->first();
        $topikSDM = Topik::where('kode', 'SDM')->first();

        $surat = SuratMasuk::create([
            'nomor_agenda' => 'AG-INT-001',
            'pengirim' => 'Test',
            'perihal' => 'Integration Test',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $umum->id,
            'status_id' => StatusSurat::where('kode', 'received')->value('id'),
            'created_by' => $user->id,
        ]);

        // Attach single topik
        $surat->topiks()->attach($topikKeuangan);
        $this->assertCount(1, $surat->fresh()->topiks);
        $this->assertTrue($surat->fresh()->topiks->contains('id', $topikKeuangan->id));

        // Attach second topik
        $surat->topiks()->attach($topikSDM);
        $this->assertCount(2, $surat->fresh()->topiks);

        // Detach one
        $surat->topiks()->detach($topikKeuangan);
        $this->assertCount(1, $surat->fresh()->topiks);
        $this->assertFalse($surat->fresh()->topiks->contains('id', $topikKeuangan->id));

        // Sync all
        $allTopiks = Topik::all()->pluck('id')->toArray();
        $surat->topiks()->sync($allTopiks);
        $this->assertCount(Topik::count(), $surat->fresh()->topiks);
    }

    public function test_is_rahasia_accessor_for_each_klasifikasi(): void
    {
        $role = \App\Models\Role::firstOrCreate(['kode' => 'admin'], ['label' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        StatusSurat::firstOrCreate(['kode' => 'received'], ['label' => 'Diterima', 'urutan' => 1]);

        $klasifikasis = Klasifikasi::all();
        foreach ($klasifikasis as $k) {
            $surat = SuratMasuk::create([
                'nomor_agenda' => 'AG-' . $k->kode,
                'pengirim' => 'Test',
                'perihal' => 'Test ' . $k->kode,
                'tanggal_diterima' => today(),
                'klasifikasi_id' => $k->id,
                'status_id' => StatusSurat::where('kode', 'received')->value('id'),
                'created_by' => $user->id,
            ]);

            $expected = in_array($k->kode, ['TERBATAS', 'RAHASIA']);
            $this->assertEquals($expected, $surat->is_rahasia, "is_rahasia should be {$expected} for klasifikasi {$k->kode}");
        }
    }
}
