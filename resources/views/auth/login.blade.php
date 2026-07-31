<x-guest-layout>
    <div class="text-center mb-4">
        @if(\App\Models\Setting::get('app_logo'))
        <a href="/" class="navbar-brand navbar-brand-autodark">
            <img src="{{ \Storage::disk('public')->url(\App\Models\Setting::get('app_logo')) }}" alt="{{ \App\Models\Setting::get('app_name', 'SiMPEL') }}" style="height: 32px;">
        </a>
        @endif
    </div>

    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Masuk ke Akun</h2>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <div class="d-flex">
                        <div>
                            <i class="ti ti-alert-circle"></i>
                        </div>
                        <div class="ms-2">
                            {{ $errors->first() }}
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" autocomplete="off">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="contoh@email.com" value="{{ old('email') }}" required autofocus autocomplete="username">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-2">
                    <label class="form-label">
                        Password
                        @if (Route::has('password.request'))
                        <span class="form-label-description">
                            <a href="{{ route('password.request') }}">Lupa password?</a>
                        </span>
                        @endif
                    </label>
                    <div class="input-group input-group-flat">
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Masukkan password" required autocomplete="current-password">
                        <span class="input-group-text">
                            <a href="#" class="link-secondary" title="Tampilkan password" data-bs-toggle="tooltip" onclick="togglePassword(event)">
                                <i id="eye-icon" class="ti ti-eye"></i>
                            </a>
                        </span>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-check">
                        <input type="checkbox" name="remember" id="remember_me" class="form-check-input">
                        <span class="form-check-label">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                @if(config('captcha.enabled'))
                <div class="mb-2">
                    <label class="form-label">Verifikasi Keamanan</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ $captchaQuestion }} =</span>
                        <input type="number" name="captcha" class="form-control @error('captcha') is-invalid @enderror" placeholder="Jawaban" required autocomplete="off">
                        @error('captcha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                @endif

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center text-secondary mt-3">
        SiMPEL — Sistem Manajemen Surat
    </div>

    <script>
        function togglePassword(e) {
            e.preventDefault();
            var pwd = document.getElementById('password');
            var icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.className = 'ti ti-eye-off';
            } else {
                pwd.type = 'password';
                icon.className = 'ti ti-eye';
            }
        }
    </script>
</x-guest-layout>
