<x-guest-layout>
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Konfirmasi Password</h2>
            <div class="text-secondary mb-4">
                {{ __('Ini area aman aplikasi. Konfirmasi password Anda sebelum melanjutkan.') }}
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label required">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
