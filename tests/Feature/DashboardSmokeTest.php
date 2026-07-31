<?php

namespace Tests\Feature;

use App\Models\KomitePersetujuan;
use App\Models\NotulensiRapim;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_dashboard_renders_with_arsip_data()
    {
        $admin = User::whereHas('role', fn ($q) => $q->where('kode', 'admin'))->first() ?? User::factory()->create();
        $this->actingAs($admin);

        KomitePersetujuan::create([
            'judul' => 'Komite Test', 'jenis' => 'pembelian',
            'tanggal_rapat' => now(), 'hasil' => 'disetujui',
            'dicatat_oleh_id' => $admin->id,
        ]);
        NotulensiRapim::create([
            'judul_rapat' => 'Rapim Test', 'tanggal' => now(),
            'ringkasan_pembahasan' => 'test', 'dicatat_oleh_id' => $admin->id,
        ]);
        SuratKeputusan::create([
            'nomor_sk' => 'SK-001', 'perihal' => 'SK Test',
            'tanggal_sk' => now(), 'status' => 'aktif', 'dicatat_oleh_id' => $admin->id,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Komite Test')
            ->assertSee('Rapim Test')
            ->assertSee('SK Test')
            ->assertSee('Surat Keluar');
    }
}
