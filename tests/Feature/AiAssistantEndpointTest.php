<?php

namespace Tests\Feature;

use App\Models\Klasifikasi;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SuratKeluar;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiAssistantEndpointTest extends TestCase
{
    use RefreshDatabase;

    private Role $unitLainRole;
    private Role $noPermRole;
    private Klasifikasi $normal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitLainRole = Role::create(['kode' => 'unit_lain', 'label' => 'Unit Lain']);
        $this->noPermRole = Role::create(['kode' => 'bebas', 'label' => 'Tanpa Izin']);

        $this->seed(PermissionSeeder::class);

        $this->normal = Klasifikasi::create([
            'kode' => 'normal',
            'nama' => 'Normal',
            'is_rahasia' => false,
        ]);
    }

    private function enableAi(): void
    {
        Setting::set('ai_enabled', '1');
        Setting::set('xai_api_key', 'test-key-123');
    }

    private function user(Role $role): User
    {
        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_endpoint_ditolak_saat_ai_dinonaktifkan(): void
    {
        $user = $this->user($this->unitLainRole);

        $this->actingAs($user)
            ->postJson('/api/ai/ask', ['question' => 'apakah ada surat?'])
            ->assertForbidden();
    }

    public function test_endpoint_ditolak_tanpa_permission(): void
    {
        $this->enableAi();
        $user = $this->user($this->noPermRole);

        $this->actingAs($user)
            ->postJson('/api/ai/ask', ['question' => 'apakah ada surat?'])
            ->assertForbidden();
    }

    public function test_endpoint_validasi_pertanyaan(): void
    {
        $this->enableAi();
        $user = $this->user($this->unitLainRole);

        $this->actingAs($user)
            ->postJson('/api/ai/ask', ['question' => ''])
            ->assertStatus(422);
    }

    public function test_endpoint_berhasil_dan_mencatat_log(): void
    {
        $this->enableAi();
        $user = $this->user($this->unitLainRole);

        SuratKeluar::create([
            'nomor_surat' => 'SKL-2026-0101',
            'perihal' => 'Penandatanganan MoU',
            'tujuan_surat' => 'Dinas PU',
            'tanggal_surat' => today(),
            'klasifikasi_id' => $this->normal->id,
            'pimpinan_pengirim_id' => $user->id,
            'created_by' => $user->id,
        ]);

        Http::fake([
            'api.x.ai/*' => Http::sequence()
                ->push([
                    'choices' => [[
                        'message' => [
                            'role' => 'assistant',
                            'content' => null,
                            'tool_calls' => [[
                                'id' => 'call_1',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'search_documents',
                                    'arguments' => json_encode(['modul' => 'surat_keluar', 'keyword' => 'MoU']),
                                ],
                            ]],
                        ],
                    ]],
                    'usage' => ['prompt_tokens' => 50, 'completion_tokens' => 10, 'total_tokens' => 60],
                ])
                ->push([
                    'choices' => [[
                        'message' => ['role' => 'assistant', 'content' => 'Ditemukan 1 surat keluar.'],
                    ]],
                    'usage' => ['prompt_tokens' => 60, 'completion_tokens' => 15, 'total_tokens' => 75],
                ]),
        ]);

        $this->actingAs($user)
            ->postJson('/api/ai/ask', ['question' => 'ada surat keluar MoU?'])
            ->assertOk()
            ->assertJsonPath('answer', 'Ditemukan 1 surat keluar.')
            ->assertJsonPath('meta.model', 'grok-4.3')
            ->assertJsonCount(1, 'links');

        $this->assertDatabaseHas('ai_chat_logs', [
            'user_id' => $user->id,
            'question' => 'ada surat keluar MoU?',
            'success' => true,
            'results_count' => 1,
            'model' => 'grok-4.3',
        ]);
    }

    public function test_endpoint_error_ai_mencatat_log_gagal(): void
    {
        $this->enableAi();
        $user = $this->user($this->unitLainRole);

        Http::fake([
            'api.x.ai/*' => Http::response(['error' => ['message' => 'boom']], 500),
        ]);

        $this->actingAs($user)
            ->postJson('/api/ai/ask', ['question' => 'ada surat?'])
            ->assertStatus(502);

        $this->assertDatabaseHas('ai_chat_logs', [
            'user_id' => $user->id,
            'question' => 'ada surat?',
            'success' => false,
        ]);
    }
}
