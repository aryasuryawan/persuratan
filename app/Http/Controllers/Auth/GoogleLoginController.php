<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleLoginController extends Controller
{
    /**
     * Redirect user ke Google untuk otorisasi.
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Proses callback dari Google.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth callback gagal', ['error' => $e->getMessage()]);

            return $this->tolakLogin('Gagal memproses login Google, silakan coba lagi.');
        }

        $email = $googleUser->getEmail();
        if (! $email) {
            return $this->tolakLogin('Akun Google ini tidak memiliki alamat email.');
        }

        if (! $this->emailTerverifikasiGoogle($googleUser)) {
            return $this->tolakLogin('Email akun Google belum terverifikasi, hubungi admin.');
        }

        if (! $this->domainDiizinkan($email)) {
            return $this->tolakLogin('Domain email Google tidak diizinkan untuk login ke sistem ini.');
        }

        $user = $this->cariUserByEmail($email);
        if (! $user) {
            return $this->tolakLogin('Akun Google ini belum terdaftar di sistem, hubungi admin.');
        }

        if (! $user->is_active) {
            return $this->tolakLogin('Akun Anda dinonaktifkan, hubungi admin.');
        }

        $this->hubungkanGoogle($user, $googleUser);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function emailTerverifikasiGoogle(SocialiteUser $googleUser): bool
    {
        $verified = $googleUser->attributes['email_verified']
            ?? (is_array($googleUser->user) ? ($googleUser->user['email_verified'] ?? null) : null);

        return filter_var($verified, FILTER_VALIDATE_BOOL) === true;
    }

    private function domainDiizinkan(string $email): bool
    {
        $allowed = config('services.google.allowed_domains', []);

        if (empty($allowed)) {
            return true;
        }

        $domain = strtolower((string) substr(strrchr($email, '@'), 1));

        return in_array($domain, array_map('strtolower', $allowed), true);
    }

    private function cariUserByEmail(string $email): ?User
    {
        return User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
    }

    private function hubungkanGoogle(User $user, SocialiteUser $googleUser): void
    {
        $changes = [];

        if ($user->google_id !== $googleUser->getId()) {
            $changes['google_id'] = $googleUser->getId();
        }

        if (blank($user->avatar_url)) {
            $changes['avatar_url'] = $googleUser->getAvatar();
        }

        if ($changes) {
            $user->update($changes);
        }

        if (is_null($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }

    private function tolakLogin(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
