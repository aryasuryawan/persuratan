<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\DisposisiFollowUp;
use App\Models\Klasifikasi;
use App\Models\Role;
use App\Models\StatusSurat;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DisposisiFollowUpPublikTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['kode' => 'sekretaris', 'label' => 'Sekretaris']);
        Role::create(['kode' => 'unit_lain', 'label' => 'Unit Lain']);

        StatusSurat::create(['kode' => 'received', 'label' => 'Diterima', 'urutan' => 1]);
        StatusSurat::create(['kode' => 'assigned', 'label' => 'Didisposisikan', 'urutan' => 2]);
        StatusSurat::create(['kode' => 'completed', 'label' => 'Selesai', 'urutan' => 3]);

        $this->normal = Klasifikasi::create([
            'kode' => 'normal',
            'nama' => 'Normal',
            'is_rahasia' => false,
        ]);
    }

    private function createDisposisi(): Disposisi
    {
        $sekretaris = User::factory()->create(['role_id' => Role::where('kode', 'sekretaris')->value('id')]);
        $pelaksana = User::factory()->create(['role_id' => Role::where('kode', 'unit_lain')->value('id')]);

        $surat = SuratMasuk::create([
            'nomor_agenda' => 'AG-'.now()->format('Ymd').'-'.random_int(1000, 9999),
            'pengirim' => 'PT Mitra Sejahtera',
            'perihal' => 'Penawaran Kerja Sama Proyek',
            'tanggal_diterima' => today(),
            'klasifikasi_id' => $this->normal->id,
            'status_id' => StatusSurat::where('kode', 'assigned')->value('id'),
            'created_by' => $sekretaris->id,
        ]);

        return Disposisi::create([
            'surat_masuk_id' => $surat->id,
            'dari_user_id' => $sekretaris->id,
            'kepada_user_id' => $pelaksana->id,
            'jenis' => 'ke_pelaksana',
            'disposisi_kepada' => [$pelaksana->name],
        ]);
    }

    public function test_halaman_publik_tanpa_login_menampilkan_form(): void
    {
        $disposisi = $this->createDisposisi();

        $response = $this->get('/follow-up/'.$disposisi->uuid);

        $response->assertOk();
        $response->assertSee('Lapor Follow Up Disposisi');
        $response->assertSee($disposisi->suratMasuk->perihal);
    }

    public function test_kirim_follow_up_publik_tanpa_login_tersimpan(): void
    {
        $disposisi = $this->createDisposisi();

        $response = $this->post('/follow-up/'.$disposisi->uuid, [
            'keterangan' => 'Surat sudah ditindaklanjuti dan dokumen siap.',
            'lampiran' => [
                UploadedFile::fake()->image('foto.jpg'),
                UploadedFile::fake()->create(
                    'laporan.docx',
                    100,
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ),
                UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertRedirect('/follow-up/'.$disposisi->uuid);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('disposisi_follow_ups', [
            'disposisi_id' => $disposisi->id,
            'user_id' => null,
            'keterangan' => 'Surat sudah ditindaklanjuti dan dokumen siap.',
        ]);

        $followUp = DisposisiFollowUp::where('disposisi_id', $disposisi->id)->first();
        $this->assertNotNull($followUp);
        $this->assertCount(3, $followUp->lampiran);

        $surat = $disposisi->suratMasuk->fresh();
        $this->assertSame('completed', $surat->status->kode);
        $this->assertNotNull($surat->completed_at);
    }

    public function test_follow_up_publik_menolak_ekstensi_tidak_diizinkan(): void
    {
        $disposisi = $this->createDisposisi();

        $response = $this->post('/follow-up/'.$disposisi->uuid, [
            'keterangan' => 'Laporan dengan file terlarang.',
            'lampiran' => [
                UploadedFile::fake()->create('script.php', 100, 'application/x-php'),
            ],
        ]);

        $response->assertSessionHasErrors('lampiran.0');
        $this->assertDatabaseMissing('disposisi_follow_ups', ['disposisi_id' => $disposisi->id]);
    }

    public function test_follow_up_publik_polyglot_tersimpan_dengan_ekstensi_dari_isi_bukan_nama_client(): void
    {
        $disposisi = $this->createDisposisi();

        $path = tempnam(sys_get_temp_dir(), 'png');
        imagepng(imagecreatetruecolor(2, 2), $path);
        $polyglot = new UploadedFile($path, 'evil.foo', 'image/png', null, true);

        $response = $this->post('/follow-up/'.$disposisi->uuid, [
            'keterangan' => 'File poliglot (PNG dengan nama ekstensi tak dikenal).',
            'lampiran' => [$polyglot],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/follow-up/'.$disposisi->uuid);

        $followUp = DisposisiFollowUp::where('disposisi_id', $disposisi->id)->first();
        $this->assertNotNull($followUp);

        $storedPath = $followUp->lampiran[0]['stored_path'];
        $this->assertStringEndsWith('.png', $storedPath);
        $this->assertStringNotContainsString('.foo', $storedPath);
        $this->assertFileExists(storage_path('app/public/'.$storedPath));
    }

    public function test_follow_up_publik_menolak_konten_php_meski_berekstensi_aman(): void
    {
        $disposisi = $this->createDisposisi();

        $path = tempnam(sys_get_temp_dir(), 'png');
        file_put_contents($path, '<?php echo "pwned"; ?>');
        $evil = new UploadedFile($path, 'foto.png', 'image/png', null, true);

        $response = $this->post('/follow-up/'.$disposisi->uuid, [
            'keterangan' => 'File PHP disamarkan jadi png.',
            'lampiran' => [$evil],
        ]);

        $response->assertSessionHasErrors('lampiran.0');
        $this->assertDatabaseMissing('disposisi_follow_ups', ['disposisi_id' => $disposisi->id]);
    }

    public function test_follow_up_publik_dibatasi_rate(): void
    {
        Cache::flush();

        for ($i = 0; $i < 5; $i++) {
            $d = $this->createDisposisi();
            $this->post('/follow-up/'.$d->uuid, ['keterangan' => 'Percobaan '.$i])
                ->assertRedirect('/follow-up/'.$d->uuid);
        }

        $d = $this->createDisposisi();
        $this->post('/follow-up/'.$d->uuid, ['keterangan' => 'Percobaan ke-6'])
            ->assertStatus(429);
    }

    public function test_follow_up_publik_link_kedaluwarsa_setelah_diisi(): void
    {
        Cache::flush();

        $disposisi = $this->createDisposisi();

        $this->post('/follow-up/'.$disposisi->uuid, ['keterangan' => 'Laporan pertama.'])
            ->assertRedirect('/follow-up/'.$disposisi->uuid)
            ->assertSessionHas('success');

        $this->post('/follow-up/'.$disposisi->uuid, ['keterangan' => 'Percobaan kedua.'])
            ->assertRedirect('/follow-up/'.$disposisi->uuid)
            ->assertSessionHas('error');

        $this->assertSame(1, DisposisiFollowUp::where('disposisi_id', $disposisi->id)->count());

        $this->get('/follow-up/'.$disposisi->uuid)
            ->assertOk()
            ->assertSee('tidak lagi aktif')
            ->assertDontSee('Kirim Laporan Follow Up');
    }
}
