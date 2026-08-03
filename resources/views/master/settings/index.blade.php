@extends('layouts.app')

@section('page-title', 'Pengaturan')

@section('page-header')
@endsection

@section('content')
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-umum" type="button" role="tab">Umum</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-email" type="button" role="tab">Email / SMTP</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ai" type="button" role="tab">AI Assistant</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-log" type="button" role="tab">Log Pengiriman Email</button>
    </li>
</ul>

<div class="tab-content">
    <!-- ===================== TAB UMUM ===================== -->
    <div class="tab-pane fade show active" id="tab-umum" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pengaturan Aplikasi</h3>
            </div>
            <form method="POST" action="{{ route('master.settings.update') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="active_tab" value="umum">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Aplikasi</label>
                                <input type="text" name="app_name" class="form-control @error('app_name') is-invalid @enderror" value="{{ old('app_name', $settings['app_name']->value ?? '') }}">
                                @error('app_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Lembaga</label>
                                <input type="text" name="lembaga_nama" class="form-control @error('lembaga_nama') is-invalid @enderror" value="{{ old('lembaga_nama', $settings['lembaga_nama']->value ?? '') }}">
                                @error('lembaga_nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Logo Aplikasi</label>
                                <input type="file" name="app_logo" class="form-control @error('app_logo') is-invalid @enderror" accept="image/jpg,image/jpeg,image/png,image/svg+xml,image/webp">
                                @error('app_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if(isset($settings['app_logo']) && $settings['app_logo']->value)
                                <div class="mt-2">
                                    <img src="{{ Storage::disk('public')->url($settings['app_logo']->value) }}" alt="Logo" style="max-height: 40px;">
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Favicon</label>
                                <input type="file" name="app_favicon" class="form-control @error('app_favicon') is-invalid @enderror" accept="image/png,image/x-icon,image/vnd.microsoft.icon,image/webp">
                                @error('app_favicon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if(isset($settings['app_favicon']) && $settings['app_favicon']->value)
                                <div class="mt-2">
                                    <img src="{{ Storage::disk('public')->url($settings['app_favicon']->value) }}" alt="Favicon" style="max-height: 32px;">
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Lembaga</label>
                        <textarea name="lembaga_alamat" class="form-control @error('lembaga_alamat') is-invalid @enderror" rows="3">{{ old('lembaga_alamat', $settings['lembaga_alamat']->value ?? '') }}</textarea>
                        @error('lembaga_alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">No. Telepon</label>
                                <input type="text" name="lembaga_telepon" class="form-control @error('lembaga_telepon') is-invalid @enderror" value="{{ old('lembaga_telepon', $settings['lembaga_telepon']->value ?? '') }}">
                                @error('lembaga_telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="lembaga_email" class="form-control @error('lembaga_email') is-invalid @enderror" value="{{ old('lembaga_email', $settings['lembaga_email']->value ?? '') }}">
                                @error('lembaga_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" name="lembaga_website" class="form-control @error('lembaga_website') is-invalid @enderror" value="{{ old('lembaga_website', $settings['lembaga_website']->value ?? '') }}">
                        @error('lembaga_website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Teks Footer</label>
                        <textarea name="footer_text" class="form-control @error('footer_text') is-invalid @enderror" rows="2">{{ old('footer_text', $settings['footer_text']->value ?? '') }}</textarea>
                        @error('footer_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Copyright Footer</label>
                        <input type="text" name="footer_copyright" class="form-control @error('footer_copyright') is-invalid @enderror" value="{{ old('footer_copyright', $settings['footer_copyright']->value ?? '') }}" placeholder="Contoh: © 2026 Nama Lembaga">
                        @error('footer_copyright') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Footer (Dokumentasi & Panduan)</label>
                        @php
                            $footerLinks = \App\Models\Setting::getArray('footer_links', []);
                            if (empty($footerLinks)) {
                                $footerLinks = [['label' => '', 'url' => ''], ['label' => '', 'url' => '']];
                            }
                        @endphp
                        <div id="footer-links-container">
                            @foreach($footerLinks as $i => $link)
                            <div class="row g-2 mb-2 footer-link-row">
                                <div class="col-5">
                                    <input type="text" name="footer_links[{{ $i }}][label]" class="form-control form-control-sm" placeholder="Label (contoh: Dokumentasi)" value="{{ $link['label'] ?? '' }}">
                                </div>
                                <div class="col-5">
                                    <input type="url" name="footer_links[{{ $i }}][url]" class="form-control form-control-sm" placeholder="URL" value="{{ $link['url'] ?? '' }}">
                                </div>
                                <div class="col-2 d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-footer-link" {{ count($footerLinks) <= 1 ? 'disabled' : '' }}>Hapus</button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-footer-link" class="btn btn-sm btn-outline-secondary">Tambah Link</button>
                        <small class="form-hint">Link yang ditampilkan di footer (Dokumentasi & Panduan). Kosongkan untuk menghapus.</small>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================== TAB EMAIL / SMTP ===================== -->
    <div class="tab-pane fade" id="tab-email" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pengaturan Email / SMTP</h3>
            </div>
            <form method="POST" action="{{ route('master.settings.update') }}" enctype="multipart/form-data" id="form-smtp">
                @csrf
                <input type="hidden" name="active_tab" value="email">
                <div class="card-body">

                    <div class="alert alert-info">
                        <strong>Email Notifikasi:</strong> Sistem akan mengirim email notifikasi disposisi ke penerima. Untuk penerima dari freetext, isi kolom email pada nama/jabatan bebas agar email terkirim.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label d-block">Aktifkan Email Notifikasi</label>
                                <label class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="smtp_enabled" value="1" {{ old('smtp_enabled', $settings['smtp_enabled']->value ?? false) ? 'checked' : '' }}>
                                    <span class="form-check-label">Ya, aktifkan pengiriman email</span>
                                </label>
                                <small class="form-hint">Jika nonaktif, email tidak akan dikirim. Log tetap tercatat sebagai failed.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Driver / Penyedia Email</label>
                                <select name="smtp_driver" class="form-select" id="smtp-driver">
                                    <option value="smtp" {{ old('smtp_driver', $settings['smtp_driver']->value ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP Standar / PHP Mailer</option>
                                    <option value="mailersend" {{ old('smtp_driver', $settings['smtp_driver']->value ?? 'smtp') === 'mailersend' ? 'selected' : '' }}>MailerSend</option>
                                    <option value="sendgrid" {{ old('smtp_driver', $settings['smtp_driver']->value ?? 'smtp') === 'sendgrid' ? 'selected' : '' }}>SendGrid</option>
                                    <option value="gmail" {{ old('smtp_driver', $settings['smtp_driver']->value ?? 'smtp') === 'gmail' ? 'selected' : '' }}>Google Mail (Gmail / Workspace)</option>
                                </select>
                                <small class="form-hint">Pilih penyedia layanan email yang digunakan.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dari Alamat (From Address)</label>
                        <input type="email" name="smtp_from_address" class="form-control @error('smtp_from_address') is-invalid @enderror" value="{{ old('smtp_from_address', $settings['smtp_from_address']->value ?? '') }}" placeholder="contoh: noreply@lembaga.org">
                        @error('smtp_from_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dari Nama (From Name)</label>
                        <input type="text" name="smtp_from_name" class="form-control @error('smtp_from_name') is-invalid @enderror" value="{{ old('smtp_from_name', $settings['smtp_from_name']->value ?? '') }}" placeholder="contoh: Sistem Informasi Surat">
                        @error('smtp_from_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- ===== SMTP STANDAR ===== --}}
                    <div id="config-smtp" class="smtp-config-block">
                        <div class="card mb-3">
                            <div class="card-header"><h3 class="card-title">Konfigurasi SMTP Standar / PHP Mailer</h3></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">SMTP Host</label>
                                            <input type="text" name="smtp_host" class="form-control @error('smtp_host') is-invalid @enderror" value="{{ old('smtp_host', $settings['smtp_host']->value ?? '') }}" placeholder="contoh: smtp.gmail.com / smtp.mailersend.net">
                                            @error('smtp_host') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">SMTP Port</label>
                                            <input type="number" name="smtp_port" class="form-control @error('smtp_port') is-invalid @enderror" value="{{ old('smtp_port', $settings['smtp_port']->value ?? '587') }}" placeholder="587">
                                            @error('smtp_port') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" name="smtp_username" class="form-control @error('smtp_username') is-invalid @enderror" value="{{ old('smtp_username', $settings['smtp_username']->value ?? '') }}" placeholder="email atau username">
                                            @error('smtp_username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" name="smtp_password" class="form-control @error('smtp_password') is-invalid @enderror" value="{{ old('smtp_password', $settings['smtp_password']->value ?? '') }}" placeholder="password atau app password">
                                            @error('smtp_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Enkripsi</label>
                                    <select name="smtp_encryption" class="form-select">
                                        <option value="tls" {{ old('smtp_encryption', $settings['smtp_encryption']->value ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (STARTTLS)</option>
                                        <option value="ssl" {{ old('smtp_encryption', $settings['smtp_encryption']->value ?? 'tls') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="none" {{ old('smtp_encryption', $settings['smtp_encryption']->value ?? 'tls') === 'none' ? 'selected' : '' }}>Tanpa Enkripsi</option>
                                    </select>
                                </div>
                                <small class="form-hint">Untuk Gmail gunakan App Password (2FA) bukan password biasa. Untuk MailerSend, port 587 + TLS.</small>
                            </div>
                        </div>
                    </div>

                    {{-- ===== MAILERSEND ===== --}}
                    <div id="config-mailersend" class="smtp-config-block" style="display:none;">
                        <div class="card mb-3">
                            <div class="card-header"><h3 class="card-title">Konfigurasi MailerSend</h3></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">API Key</label>
                                    <input type="password" name="mailersend_api_key" class="form-control @error('mailersend_api_key') is-invalid @enderror" value="{{ old('mailersend_api_key', $settings['mailersend_api_key']->value ?? '') }}" placeholder="mlsn.xxxx">
                                    @error('mailersend_api_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-hint">Dapatkan dari dashboard MailerSend &gt; API Tokens. Verifikasi domain sender terlebih dahulu. Host otomatis: <code>smtp.mailersend.net:587</code> (API key dipakai sebagai username &amp; password).</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== SENDGRID ===== --}}
                    <div id="config-sendgrid" class="smtp-config-block" style="display:none;">
                        <div class="card mb-3">
                            <div class="card-header"><h3 class="card-title">Konfigurasi SendGrid</h3></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">API Key</label>
                                    <input type="password" name="sendgrid_api_key" class="form-control @error('sendgrid_api_key') is-invalid @enderror" value="{{ old('sendgrid_api_key', $settings['sendgrid_api_key']->value ?? '') }}" placeholder="SG.xxxx">
                                    @error('sendgrid_api_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-hint">Dapatkan dari dashboard SendGrid &gt; Settings &gt; API Keys. Verifikasi <strong>Sender Identity</strong> untuk alamat From terlebih dahulu. Host otomatis: <code>smtp.sendgrid.net:587</code>, username <code>apikey</code>.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== GMAIL / GOOGLE MAIL ===== --}}
                    <div id="config-gmail" class="smtp-config-block" style="display:none;">
                        <div class="card mb-3">
                            <div class="card-header"><h3 class="card-title">Konfigurasi Google Mail (Gmail / Workspace)</h3></div>
                            <div class="card-body">
                                <div class="alert alert-warning py-2">
                                    <small>Host otomatis <code>smtp.gmail.com</code>. Wajib memakai <strong>App Password</strong> (bukan password akun).</small>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Username / Email</label>
                                            <input type="text" name="smtp_username" class="form-control" value="{{ old('smtp_username', $settings['smtp_username']->value ?? '') }}" placeholder="nama@gmail.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">App Password</label>
                                            <input type="password" name="smtp_password" class="form-control" value="{{ old('smtp_password', $settings['smtp_password']->value ?? '') }}" placeholder="16 karakter app password">
                                            <small class="form-hint">Buat di Google Account &gt; Security &gt; App Passwords (butuh 2FA aktif).</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Port</label>
                                    <input type="number" name="smtp_port" class="form-control" value="{{ old('smtp_port', $settings['smtp_port']->value ?? '587') }}">
                                    <small class="form-hint">587 + TLS (disarankan) atau 465 + SSL.</small>
                                </div>
                                <hr>
                                <p class="form-hint"><strong>Alternatif OAuth2 (opsional):</strong> jika Anda memakai Google Workspace dengan OAuth.</p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Client ID</label>
                                            <input type="text" name="gmail_client_id" class="form-control" value="{{ old('gmail_client_id', $settings['gmail_client_id']->value ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Client Secret</label>
                                            <input type="text" name="gmail_client_secret" class="form-control" value="{{ old('gmail_client_secret', $settings['gmail_client_secret']->value ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Refresh Token</label>
                                    <input type="text" name="gmail_refresh_token" class="form-control" value="{{ old('gmail_refresh_token', $settings['gmail_refresh_token']->value ?? '') }}">
                                    <small class="form-hint">Untuk pengiriman via API Gmail OAuth2. Jika kosong, sistem memakai App Password.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan Email</button>
                    <button type="button" class="btn btn-outline-primary" id="btn-test-smtp">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;vertical-align:-2px;margin-right:4px;"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/></svg>
                        Test Koneksi &amp; Kirim Email Uji
                    </button>
                    <span id="smtp-test-result" class="ms-2"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================== TAB AI ASSISTANT ===================== -->
    <div class="tab-pane fade" id="tab-ai" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pengaturan AI Assistant</h3>
            </div>
            <form method="POST" action="{{ route('master.settings.update') }}" id="form-ai">
                @csrf
                <input type="hidden" name="active_tab" value="ai">
                <div class="card-body">

                    <div class="alert alert-info">
                        <strong>AI Assistant:</strong> floating chat bubble untuk bertanya soal data surat/dokumen. Jawaban mengikuti hak akses (role) masing-masing user. API key disimpan di server — tidak pernah bocor ke browser.
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Aktifkan AI Assistant</label>
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="ai_enabled" value="1" {{ old('ai_enabled', $settings['ai_enabled']->value ?? false) ? 'checked' : '' }}>
                            <span class="form-check-label">Ya, aktifkan fitur AI Assistant</span>
                        </label>
                        <small class="form-hint">Jika nonaktif, floating bubble tidak ditampilkan dan endpoint AI menolak permintaan.</small>
                    </div>

                    @php
                    $aiProviders = [
                        'xai' => [
                            'label' => 'xAI (Grok) — berbayar',
                            'base_url' => 'https://api.x.ai/v1',
                            'key_placeholder' => 'xai-xxxxxxxxxxxx',
                            'key_hint' => 'Dapatkan dari <a href="https://console.x.ai" target="_blank" rel="noopener">console.x.ai</a> &gt; API Keys. Semua model xAI berbayar.',
                            'models' => [
                                'grok-4.3' => 'Grok 4.3 ($1.25/M input) — seimbang, default',
                                'grok-4.5' => 'Grok 4.5 ($2.00/M input) — flagship',
                                'grok-build-0.1' => 'Grok Build 0.1 ($1.00/M input) — coding/budget',
                            ],
                        ],
                        'groq' => [
                            'label' => 'Groq (Llama/Gemma) — GRATIS',
                            'base_url' => 'https://api.groq.com/openai/v1',
                            'key_placeholder' => 'gsk_xxxxxxxxxxxx',
                            'key_hint' => 'Dapatkan dari <a href="https://console.groq.com/keys" target="_blank" rel="noopener">console.groq.com</a> &gt; API Keys. Free tier GRATIS dengan rate limit.',
                            'models' => [
                                'llama-3.3-70b-versatile' => 'Llama 3.3 70B — gratis',
                                'llama-3.1-8b-instant' => 'Llama 3.1 8B — gratis, cepat',
                                'gemma-2-9b-it' => 'Gemma 2 9B — gratis, ringan',
                            ],
                        ],
                        'google' => [
                            'label' => 'Google Gemini — GRATIS',
                            'base_url' => 'https://generativelanguage.googleapis.com/v1beta/openai',
                            'key_placeholder' => 'AIzaSyxxxxxxxxxxxx',
                            'key_hint' => 'Dapatkan dari <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener">aistudio.google.com/apikey</a>. Free tier GRATIS, rate limit ketat.',
                            'models' => [
                                'gemini-2.5-flash' => 'Gemini 2.5 Flash — gratis, cepat',
                                'gemini-2.0-flash' => 'Gemini 2.0 Flash — gratis, hemat',
                            ],
                        ],
                        'openrouter' => [
                            'label' => 'OpenRouter — ada model GRATIS',
                            'base_url' => 'https://openrouter.ai/api/v1',
                            'key_placeholder' => 'sk-or-xxxxxxxxxxxx',
                            'key_hint' => 'Dapatkan dari <a href="https://openrouter.ai/keys" target="_blank" rel="noopener">openrouter.ai/keys</a>. Model gratis pakai slug akhiran <code>:free</code>.',
                            'models' => [
                                'meta-llama/llama-3.3-70b-instruct:free' => 'Llama 3.3 70B (:free) — gratis',
                                'deepseek/deepseek-chat-v3-0324:free' => 'DeepSeek V3 (:free) — gratis',
                            ],
                        ],
                    ];
                    $currentProvider = $settings->get('ai_provider')?->value ?? 'xai';
                    if (!isset($aiProviders[$currentProvider])) { $currentProvider = 'xai'; }
                    $currentModel = old('ai_model', $settings['ai_model']->value ?? 'grok-4.3');
                    @endphp

                    <div class="mb-3">
                        <label class="form-label">Provider AI</label>
                        <select name="ai_provider" id="ai-provider" class="form-select @error('ai_provider') is-invalid @enderror">
                            @foreach($aiProviders as $value => $p)
                            <option value="{{ $value }}" data-base-url="{{ $p['base_url'] }}" data-key-placeholder="{{ $p['key_placeholder'] }}" {{ $currentProvider === $value ? 'selected' : '' }}>{{ $p['label'] }}</option>
                            @endforeach
                        </select>
                        @error('ai_provider') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-hint">Ganti provider akan otomatis menyesuaikan Base URL &amp; daftar model. Semua provider OpenAI-compatible.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">API Key</label>
                        <input type="password" name="xai_api_key" id="ai-api-key" class="form-control @error('xai_api_key') is-invalid @enderror" value="{{ old('xai_api_key', $settings['xai_api_key']->value ?? '') }}" placeholder="{{ $aiProviders[$currentProvider]['key_placeholder'] }}">
                        @error('xai_api_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @foreach($aiProviders as $value => $p)
                        <small id="ai-hint-{{ $value }}" class="form-hint {{ $currentProvider === $value ? '' : 'd-none' }}">{!! $p['key_hint'] !!}</small>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Base URL</label>
                        <input type="url" name="ai_base_url" id="ai-base-url" class="form-control @error('ai_base_url') is-invalid @enderror" value="{{ old('ai_base_url', $settings['ai_base_url']->value ?? 'https://api.x.ai/v1') }}" placeholder="https://api.x.ai/v1">
                        @error('ai_base_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-hint">Otomatis terisi saat ganti provider. Bisa diedit manual untuk gateway/proxy lain.</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Model</label>
                                <select name="ai_model" id="ai-model" class="form-select @error('ai_model') is-invalid @enderror">
                                    @foreach($aiProviders as $pValue => $p)
                                        @foreach($p['models'] as $mValue => $mLabel)
                                        <option value="{{ $mValue }}" data-provider="{{ $pValue }}" {{ $currentModel === $mValue ? 'selected' : '' }}>{{ $mLabel }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                                @error('ai_model') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Tokens (output)</label>
                                <input type="number" name="ai_max_tokens" class="form-control @error('ai_max_tokens') is-invalid @enderror" value="{{ old('ai_max_tokens', $settings['ai_max_tokens']->value ?? '1024') }}" min="1" max="32000">
                                @error('ai_max_tokens') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Timeout (detik)</label>
                                <input type="number" name="ai_timeout" class="form-control @error('ai_timeout') is-invalid @enderror" value="{{ old('ai_timeout', $settings['ai_timeout']->value ?? '30') }}" min="1" max="120">
                                @error('ai_timeout') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rate Limit (pertanyaan/menit per user)</label>
                                <input type="number" name="ai_rate_limit" class="form-control @error('ai_rate_limit') is-invalid @enderror" value="{{ old('ai_rate_limit', $settings['ai_rate_limit']->value ?? '10') }}" min="1" max="1000">
                                @error('ai_rate_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-hint">Mencegah pemakaian API key berlebihan.</small>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan AI</button>
                    <button type="button" class="btn btn-outline-primary" id="btn-test-ai">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;vertical-align:-2px;margin-right:4px;"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/></svg>
                        Test Koneksi AI
                    </button>
                    <span id="ai-test-result" class="ms-2"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================== TAB LOG EMAIL ===================== -->
    <div class="tab-pane fade" id="tab-log" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Log Pengiriman Email</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Jenis</th>
                            <th>Penerima</th>
                            <th>Email</th>
                            <th>Subjek</th>
                            <th>Status</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emailLogs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $log->type === 'disposisi' ? 'bg-blue' : 'bg-secondary' }}">
                                    {{ $log->type === 'disposisi' ? 'Disposisi' : ucfirst($log->type) }}
                                </span>
                            </td>
                            <td>{{ $log->recipient_name }}</td>
                            <td>{{ $log->recipient_email }}</td>
                            <td style="max-width:220px;"><span class="text-truncate d-inline-block align-middle" style="max-width:220px;">{{ $log->subject }}</span></td>
                            <td>
                                @if($log->status === 'sent')
                                <span class="badge bg-success">Terkirim</span>
                                @elseif($log->status === 'failed')
                                <span class="badge bg-danger">Gagal</span>
                                @else
                                <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($log->status === 'failed' && $log->error_message)
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="popover" data-bs-placement="left" data-bs-trigger="focus" title="Pesan Error" data-bs-content="{{ $log->error_message }}">
                                    Error
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada log pengiriman email.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($emailLogs->hasPages())
            <div class="card-footer d-flex align-items-center">
                <div class="text-muted">
                    Menampilkan <strong>{{ $emailLogs->firstItem() }}</strong> sampai <strong>{{ $emailLogs->lastItem() }}</strong> dari <strong>{{ $emailLogs->total() }}</strong> data
                </div>
                <ul class="pagination ms-auto mb-0">
                    {{ $emailLogs->appends(['tab' => 'log'])->links() }}
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== Footer links =====
    const container = document.getElementById('footer-links-container');
    const addButton = document.getElementById('add-footer-link');
    if (container && addButton) {
        let linkCount = container.querySelectorAll('.footer-link-row').length;

        addButton.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'row g-2 mb-2 footer-link-row';
            newRow.innerHTML = `
                <div class="col-5">
                    <input type="text" name="footer_links[${linkCount}][label]" class="form-control form-control-sm" placeholder="Label (contoh: Dokumentasi)">
                </div>
                <div class="col-5">
                    <input type="url" name="footer_links[${linkCount}][url]" class="form-control form-control-sm" placeholder="URL">
                </div>
                <div class="col-2 d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-footer-link">Hapus</button>
                </div>
            `;
            container.appendChild(newRow);
            linkCount++;
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            const rows = container.querySelectorAll('.footer-link-row');
            rows.forEach((row) => {
                const btn = row.querySelector('.remove-footer-link');
                btn.disabled = rows.length <= 1;
                btn.onclick = function() {
                    container.removeChild(row);
                    updateRemoveButtons();
                };
            });
        }

        container.querySelectorAll('.remove-footer-link').forEach(btn => {
            btn.onclick = function() {
                const row = this.closest('.footer-link-row');
                container.removeChild(row);
                updateRemoveButtons();
            };
        });

        updateRemoveButtons();
    }

    // ===== SMTP driver switch =====
    const driverSelect = document.getElementById('smtp-driver');
    const configBlocks = {
        smtp: document.getElementById('config-smtp'),
        mailersend: document.getElementById('config-mailersend'),
        sendgrid: document.getElementById('config-sendgrid'),
        gmail: document.getElementById('config-gmail'),
    };

    function switchSmtpConfig() {
        const driver = driverSelect.value;
        Object.keys(configBlocks).forEach(function(k) {
            if (configBlocks[k]) configBlocks[k].style.display = (k === driver) ? '' : 'none';
        });
    }

    if (driverSelect) {
        driverSelect.addEventListener('change', switchSmtpConfig);
        switchSmtpConfig();
    }

    // ===== Test SMTP =====
    const btnTest = document.getElementById('btn-test-smtp');
    if (btnTest) {
        btnTest.addEventListener('click', function() {
            const resultEl = document.getElementById('smtp-test-result');
            btnTest.disabled = true;
            btnTest.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menguji...';
            resultEl.innerHTML = '';

            fetch('{{ route("master.settings.test-smtp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    resultEl.innerHTML = '<span class="text-success"><strong>Berhasil:</strong> ' + data.message + '</span>';
                } else {
                    resultEl.innerHTML = '<span class="text-danger"><strong>Gagal:</strong> ' + data.message + '</span>';
                }
            })
            .catch(function() {
                resultEl.innerHTML = '<span class="text-danger"><strong>Gagal:</strong> Terjadi kesalahan saat menguji koneksi.</span>';
            })
            .finally(function() {
                btnTest.disabled = false;
                btnTest.innerHTML = 'Test Koneksi &amp; Kirim Email Uji';
            });
        });
    }

    // ===== AI Provider switcher =====
    const providerSel = document.getElementById('ai-provider');
    if (providerSel) {
        const baseUrlInput = document.getElementById('ai-base-url');
        const apiKeyInput = document.getElementById('ai-api-key');
        const modelSel = document.getElementById('ai-model');
        const hints = {};
        document.querySelectorAll('[id^="ai-hint-"]').forEach(function(el) {
            hints[el.id.replace('ai-hint-', '')] = el;
        });

        function applyProvider() {
            const p = providerSel.value;
            const opt = providerSel.options[providerSel.selectedIndex];
            baseUrlInput.value = opt.dataset.baseUrl;
            apiKeyInput.placeholder = opt.dataset.keyPlaceholder;
            Object.keys(hints).forEach(function(k) {
                hints[k].classList.toggle('d-none', k !== p);
            });
            let selectedVisible = false;
            for (let i = 0; i < modelSel.options.length; i++) {
                const o = modelSel.options[i];
                const visible = o.dataset.provider === p;
                o.hidden = !visible;
                if (visible && o.selected) selectedVisible = true;
            }
            if (!selectedVisible) {
                for (let i = 0; i < modelSel.options.length; i++) {
                    if (modelSel.options[i].dataset.provider === p) {
                        modelSel.options[i].selected = true;
                        break;
                    }
                }
            }
        }

        providerSel.addEventListener('change', applyProvider);
        applyProvider();
    }

    // ===== Test AI =====
    const btnTestAi = document.getElementById('btn-test-ai');
    if (btnTestAi) {
        btnTestAi.addEventListener('click', function() {
            const resultEl = document.getElementById('ai-test-result');
            btnTestAi.disabled = true;
            btnTestAi.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menguji...';
            resultEl.innerHTML = '';

            fetch('{{ route("master.settings.test-ai") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(function(r) {
                return r.json().catch(function() {
                    return { success: false, message: 'Respons tidak valid (status ' + r.status + ').' };
                });
            })
            .then(function(data) {
                if (data.success) {
                    resultEl.innerHTML = '<span class="text-success"><strong>Berhasil:</strong> ' + data.message + '</span>';
                } else {
                    resultEl.innerHTML = '<span class="text-danger"><strong>Gagal:</strong> ' + data.message + '</span>';
                }
            })
            .catch(function() {
                resultEl.innerHTML = '<span class="text-danger"><strong>Gagal:</strong> Terjadi kesalahan saat menguji koneksi.</span>';
            })
            .finally(function() {
                btnTestAi.disabled = false;
                btnTestAi.innerHTML = 'Test Koneksi AI';
            });
        });
    }

    // ===== Restore active tab after redirect =====
    const activeTab = '{{ session("active_tab", "umum") }}';
    const tabMap = {
        umum: 'tab-umum',
        email: 'tab-email',
        ai: 'tab-ai',
        log: 'tab-log',
    };
    if (tabMap[activeTab]) {
        const target = document.getElementById(tabMap[activeTab]);
        if (target) {
            document.querySelectorAll('#tab-umum, #tab-email, #tab-ai, #tab-log').forEach(function(t) {
                t.classList.remove('show', 'active');
            });
            document.querySelectorAll('.nav-tabs .nav-link').forEach(function(b) {
                b.classList.remove('active');
            });
            target.classList.add('show', 'active');
            document.querySelector('.nav-tabs .nav-link[data-bs-target="#' + tabMap[activeTab] + '"]').classList.add('active');
        }
    }

    // ===== Popovers for error messages =====
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.forEach(function(el) {
        new bootstrap.Popover(el);
    });
});
</script>
@endpush