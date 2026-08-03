<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\EmailNotificationLog;
use App\Models\Setting;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        $emailLogs = EmailNotificationLog::latest('created_at')->paginate(20);
        return view('master.settings.index', compact('settings', 'emailLogs'));
    }

    public function update(Request $request)
    {
        $rules = [
            'app_name' => 'nullable|string|max:255',
            'lembaga_nama' => 'nullable|string|max:255',
            'lembaga_alamat' => 'nullable|string',
            'lembaga_telepon' => 'nullable|string|max:50',
            'lembaga_email' => 'nullable|email|max:255',
            'lembaga_website' => 'nullable|url|max:255',
            'footer_text' => 'nullable|string',
            'footer_copyright' => 'nullable|string|max:255',
            'footer_links' => 'nullable|array',
            'footer_links.*.label' => 'nullable|string|max:100',
            'footer_links.*.url' => 'nullable|url|max:255',
            'app_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'app_favicon' => 'nullable|file|mimes:png,ico,webp|max:512',
            'smtp_enabled' => 'nullable|boolean',
            'smtp_driver' => 'nullable|in:smtp,mailersend,sendgrid,gmail',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl,none',
            'smtp_from_address' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
            'mailersend_api_key' => 'nullable|string|max:255',
            'sendgrid_api_key' => 'nullable|string|max:255',
            'gmail_client_id' => 'nullable|string|max:255',
            'gmail_client_secret' => 'nullable|string|max:255',
            'gmail_refresh_token' => 'nullable|string',
            'ai_enabled' => 'nullable|boolean',
            'ai_provider' => 'nullable|in:xai,groq,google,openrouter',
            'xai_api_key' => 'nullable|string|max:255',
            'ai_base_url' => 'nullable|url|max:255',
            'ai_model' => 'nullable|string|max:100',
            'ai_max_tokens' => 'nullable|integer|min:1|max:32000',
            'ai_timeout' => 'nullable|integer|min:1|max:120',
            'ai_rate_limit' => 'nullable|integer|min:1|max:1000',
        ];

        $data = $request->validate($rules);

        // Checkbox smtp_enabled: set 0 when unchecked (hanya bila form email dikirim)
        if ($request->has('smtp_driver') || $request->boolean('smtp_enabled')) {
            $data['smtp_enabled'] = $request->boolean('smtp_enabled') ? '1' : '0';
        }

        // Checkbox ai_enabled: set 0 when unchecked (hanya bila form AI dikirim)
        if ($request->has('ai_model') || $request->boolean('ai_enabled')) {
            $data['ai_enabled'] = $request->boolean('ai_enabled') ? '1' : '0';
        }

        foreach ($data as $key => $value) {
            if (in_array($key, ['app_logo', 'app_favicon'])) {
                if ($request->hasFile($key)) {
                    $file = $request->file($key);
                    if (!$file->isValid() || $file->getSize() <= 0) {
                        return redirect()->route('master.settings.index')
                            ->with('error', 'File tidak valid atau kosong. Silakan pilih file lain.');
                    }
                    $old = Setting::get($key);
                    if ($old) {
                        Storage::disk('public')->delete($old);
                    }
                    $ext = $file->getClientOriginalExtension() ?: 'png';
                    $filename = $key . '_' . date('Ymd_His') . '.' . $ext;
                    $destDir = storage_path('app/public/settings');
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    $file->move($destDir, $filename);
                    Setting::set($key, 'settings/' . $filename);
                }
                continue;
            }

            if ($value !== null && $value !== '') {
                if ($key === 'footer_links') {
                    Setting::set($key, json_encode(array_values(array_filter($value, fn($link) => !empty($link['label']) || !empty($link['url'])))));
                } else {
                    Setting::set($key, $value);
                }
            }
        }

        return redirect()->route('master.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.')
            ->with('active_tab', $request->input('active_tab', 'umum'));
    }

    public function testSmtp(EmailService $emailService)
    {
        $result = $emailService->testConnection();
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'],
        ], 500);
    }

    public function testAi()
    {
        $apiKey = Setting::get('xai_api_key');
        $baseUrl = rtrim(Setting::get('ai_base_url', 'https://api.x.ai/v1'), '/');
        $model = Setting::get('ai_model', 'grok-4.3');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key belum diisi. Simpan API key terlebih dahulu.',
            ], 422);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->withToken($apiKey)
                ->post($baseUrl . '/chat/completions', [
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => 'ping']],
                    'max_tokens' => 5,
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => "Koneksi berhasil. Model '{$model}' merespons dengan baik.",
                ]);
            }

            $error = $response->json('error.message') ?? $response->body();
            return response()->json([
                'success' => false,
                'message' => "Gagal ({$response->status()}): {$error}",
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}