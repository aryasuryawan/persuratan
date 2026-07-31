<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['kode' => 'admin', 'label' => 'Admin']);
    }

    private function googleUser(array $overrides = []): SocialiteUser
    {
        return (new SocialiteUser)->map(array_merge([
            'id' => 'google-12345',
            'name' => 'Admin Esurat',
            'email' => 'admin@esurat.test',
            'avatar' => 'https://lh3.googleusercontent.com/avatar/abc',
            'email_verified' => true,
        ], $overrides))->setToken('fake-token');
    }

    private function loginWithGoogle(array $googleOverrides = []): TestResponse
    {
        Socialite::fake('google', $this->googleUser($googleOverrides));

        return $this->get(route('google.callback'));
    }

    public function test_google_login_email_cocok_user_terdaftar_berhasil_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@esurat.test',
            'role_id' => $this->adminRole->id,
        ]);

        $response = $this->loginWithGoogle();

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_login_menyimpan_google_id_dan_avatar_url(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@esurat.test',
            'role_id' => $this->adminRole->id,
        ]);

        $this->loginWithGoogle();

        $user->refresh();
        $this->assertSame('google-12345', $user->google_id);
        $this->assertSame('https://lh3.googleusercontent.com/avatar/abc', $user->avatar_url);
    }

    public function test_google_login_tidak_menimpa_avatar_yang_sudah_ada(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@esurat.test',
            'role_id' => $this->adminRole->id,
            'avatar_url' => 'https://profil.lama/avatar.jpg',
        ]);

        $this->loginWithGoogle();

        $user->refresh();
        $this->assertSame('https://profil.lama/avatar.jpg', $user->avatar_url);
    }

    public function test_google_login_email_tidak_terdaftar_ditolak_dan_tidak_membuat_user_baru(): void
    {
        $response = $this->loginWithGoogle(['email' => 'tidak-kenal@esurat.test']);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_google_login_user_nonaktif_ditolak(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@esurat.test',
            'role_id' => $this->adminRole->id,
            'is_active' => false,
        ]);

        $response = $this->loginWithGoogle();

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $user->refresh();
        $this->assertNull($user->google_id);
    }

    public function test_google_login_email_belum_terverifikasi_ditolak(): void
    {
        User::factory()->create([
            'email' => 'admin@esurat.test',
            'role_id' => $this->adminRole->id,
        ]);

        $response = $this->loginWithGoogle(['email_verified' => false]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_google_login_domain_tidak_diizinkan_ditolak(): void
    {
        config(['services.google.allowed_domains' => ['dompetdhuafa.org']]);

        $user = User::factory()->create([
            'email' => 'admin@gmail.com',
            'role_id' => $this->adminRole->id,
        ]);

        $response = $this->loginWithGoogle(['email' => 'admin@gmail.com']);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $user->refresh();
        $this->assertNull($user->google_id);
    }

    public function test_google_login_email_cocok_case_insensitive(): void
    {
        $user = User::factory()->create([
            'email' => 'Admin@ESURAT.test',
            'role_id' => $this->adminRole->id,
        ]);

        $this->loginWithGoogle(['email' => 'admin@esurat.test']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_google_login_mengisi_email_verified_at_yang_kosong(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@esurat.test',
            'role_id' => $this->adminRole->id,
            'email_verified_at' => null,
        ]);

        $this->loginWithGoogle();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }
}
