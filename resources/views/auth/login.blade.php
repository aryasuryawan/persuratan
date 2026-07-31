<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-gray-900">{{ __('Selamat Datang') }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ __('Silakan masuk ke akun SiMPEL Anda') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($errors->any())
        <div class="mb-4 flex items-center gap-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
            <i class="ti ti-alert-circle flex-shrink-0"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <i class="ti ti-mail text-lg"></i>
                </div>
                <x-text-input id="email" class="block w-full pl-10" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="contoh@email.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <i class="ti ti-lock text-lg"></i>
                </div>
                <x-text-input id="password" class="block w-full pl-10 pr-10" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password" />
                <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i id="eye-icon" class="ti ti-eye text-lg"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#206bc4] shadow-sm focus:ring-[#206bc4]" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-[#206bc4] hover:text-[#1a5aa8] hover:underline" href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <button type="submit" id="login-btn" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-[#206bc4] border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-[#1a5aa8] focus:bg-[#1a5aa8] active:bg-[#1a5aa8] focus:outline-none focus:ring-2 focus:ring-[#206bc4] focus:ring-offset-2 transition ease-in-out duration-150">
                <span id="btn-text">{{ __('Masuk') }}</span>
                <span id="btn-spinner" class="hidden">
                    <i class="ti ti-loader animate-spin"></i>
                    <span class="ms-2">{{ __('Memproses...') }}</span>
                </span>
            </button>
        </div>
    </form>

    <script>
        function togglePassword() {
            var pwd = document.getElementById('password');
            var icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.className = 'ti ti-eye-off text-lg';
            } else {
                pwd.type = 'password';
                icon.className = 'ti ti-eye text-lg';
            }
        }
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('btn-text').classList.add('hidden');
            document.getElementById('btn-spinner').classList.remove('hidden');
            document.getElementById('login-btn').disabled = true;
        });
    </script>
</x-guest-layout>
