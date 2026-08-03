<?php

namespace Tests\Feature;

use App\Models\Klasifikasi;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SuratKeluar;
use App\Models\User;
use App\Services\AiAssistantService;
use App\Services\AiException;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiAssistantServiceTest extends TestCase
{
    use RefreshDatabase;

    private Role $unitLainRole;
    private Klasifikasi $normal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitLainRole = Role::create(['kode' => 'unit_lain', 'label' => 'Unit Lain']);
        $this->seed(PermissionSeeder::class);

        $this->normal = Klasifikasi::create([
            'kode' => 'normal',
            'nama' => 'Normal',
            'is_rahasia' => false,
        ]);

        Setting::set('xai_api_key', 'test-key-123');
        Setting::set('ai_base_url', 'https://api.x.ai/v1');
        Setting::set('ai_model', 'grok-4.3');
    }

    private function user(): User
    {
        return User::factory()->create(['role_id' => $this->unitLainRole->id]);
    }

    private function createSuratKeluar(): SuratKeluar
    {
        return SuratKeluar::create([
            'nomor_surat' => 'SKL-2026-0042',
            'perihal' => 'Kerja Sama Pembangunan Jalan',
            'tujuan_surat' => 'Dinas PU',
            'tanggal_surat' => today(),
            'klasifikasi_id' => $this->normal->id,
            'pimpinan_pengirim_id' => $this->user()->id,
            'created_by' => $this->user()->id,
        ]);
    }

    private function toolCallPayload(string $modul, string $keyword, string $toolCallId = 'call_1'): array
    {
        return [
            'choices' => [[
                'message' => [
                    'role' => 'assistant',
                    'content' => null,
                    'tool_calls' => [[
                        'id' => $toolCallId,
                        'type' => 'function',
                        'function' => [
                            'name' => 'search_documents',
                            'arguments' => json_encode(['modul' => $modul, 'keyword' => $keyword, 'limit' => 5]),
                        ],
                    ]],
                ],
            ]],
            'usage' => ['prompt_tokens' => 100, 'completion_tokens' => 20, 'total_tokens' => 120],
        ];
    }

    private function finalAnswerPayload(string $answer): array
    {
        return [
            'choices' => [[
                'message' => ['role' => 'assistant', 'content' => $answer],
            ]],
            'usage' => ['prompt_tokens' => 200, 'completion_tokens' => 50, 'total_tokens' => 250],
        ];
    }

    public function test_ask_panggil_tool_dan_kembalikan_link(): void
    {
        $surat = $this->createSuratKeluar();
        $user = $this->user();

        Http::fake([
            'api.x.ai/*' => Http::sequence()
                ->push($this->toolCallPayload('surat_keluar', 'Kerja Sama'))
                ->push($this->finalAnswerPayload('Ditemukan 1 surat keluar terkait kerja sama.')),
        ]);

        $result = app(AiAssistantService::class)->ask($user, 'ada surat keluar tentang kerja sama?');

        $this->assertSame('Ditemukan 1 surat keluar terkait kerja sama.', $result['answer']);
        $this->assertCount(1, $result['links']);
        $this->assertSame(route('surat-keluar.show', $surat->uuid), $result['links'][0]['url']);
        $this->assertSame(['surat_keluar'], $result['modules_accessed']);
        $this->assertSame(1, $result['results_count']);
    }

    public function test_ask_tanpa_tool_langsung_jawab(): void
    {
        $user = $this->user();

        Http::fake([
            'api.x.ai/*' => Http::response(
                $this->finalAnswerPayload('Saya hanya membantu soal data surat internal.')
            ),
        ]);

        $result = app(AiAssistantService::class)->ask($user, 'siapa presiden indonesia?');

        $this->assertSame('Saya hanya membantu soal data surat internal.', $result['answer']);
        $this->assertEmpty($result['links']);
        $this->assertEmpty($result['modules_accessed']);
    }

    public function test_ask_terlalu_banyak_tool_round_melempar_exception(): void
    {
        $user = $this->user();

        Http::fake([
            'api.x.ai/*' => Http::response($this->toolCallPayload('surat_keluar', 'Kerja Sama'), 200),
        ]);

        $this->expectException(AiException::class);

        app(AiAssistantService::class)->ask($user, 'cari terus?');
    }

    public function test_ask_tanpa_api_key_melempar_exception(): void
    {
        Setting::set('xai_api_key', null);
        $user = $this->user();

        $this->expectException(AiException::class);

        app(AiAssistantService::class)->ask($user, 'apakah ada surat?');
    }

    public function test_ask_error_429_melempar_exception(): void
    {
        $user = $this->user();

        Http::fake([
            'api.x.ai/*' => Http::response([
                'error' => ['message' => 'rate limited'],
            ], 429),
        ]);

        $this->expectException(AiException::class);
        $this->expectExceptionMessage('Terlalu banyak permintaan');

        app(AiAssistantService::class)->ask($user, 'apakah ada surat?');
    }
}
