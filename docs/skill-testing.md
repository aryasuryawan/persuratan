# Skill: Testing Backend, Fungsi, dan Frontend — SiMPEL

> Dokumen panduan untuk menulis & menjalankan pengujian pada proyek SiMPEL (Laravel 13).
> Baca file ini sebelum menulis test apa pun. Ikuti pola yang sudah ada — jangan bikin pola baru tanpa alasan.

---

## 1. Ringkasan Stack Testing

| Lapisan | Tools | Lokasi |
|---|---|---|
| Backend (HTTP/Controller) | PHPUnit 12 + Laravel TestCase | `tests/Feature/` |
| Fungsi (Service/Action/Helper) | PHPUnit + RefreshDatabase | `tests/Feature/` (Service Test) & `tests/Unit/` |
| E2E / Browser (frontend + alur user) | Laravel Dusk + ChromeDriver | `tests/Browser/` |
| Frontend (JS module) | Manual via Blade + Dusk smoke | `resources/js/simpel/`, `tests/Browser/` |
| Database test | SQLite in-memory (`:memory:`) | didefinisikan di `phpunit.xml` |
| Database Dusk | SQLite file `database/dusk.sqlite` | didefinisikan di `.env.dusk.local` |

Konfigurasi sudah ada di `phpunit.xml`:
- `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` → test tidak menyentuh MySQL.
- `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=array`.
- `CAPTCHA_ENABLED=false`, `TELESCOPE_ENABLED=false`.

---

## 2. Menjalankan Test

```bash
# Semua test (disarankan, sesuai composer script)
composer test

# Langsung artisan test
php artisan test

# Satu file
php artisan test --filter=DocumentSearchServiceTest

# Satu method
php artisan test --filter=test_admin_dapat_mencari_surat_keluar

# Hanya Feature suite
php artisan test --testsuite=Feature

# Hanya Unit suite
php artisan test --testsuite=Unit

# Dusk (E2E browser) — wajib ChromeDriver jalan
php artisan dusk

# Satu file Dusk
php artisan dusk --filter=LoginTest

# Satu method Dusk
php artisan dusk --filter=test_login_admin_sukses_menuju_dashboard

# Dengan file view cache (jika tampilan tidak update)
php artisan view:clear
```

> Dusk memakai `.env.dusk.local` (DB sqlite file `database/dusk.sqlite`) secara otomatis — tidak menyentuh MySQL. Pastikan `database/dusk.sqlite` ada sebelum run. Jika ChromeDriver error, cek port 9515 bebas.

---

## 3. Backend Testing (Feature / HTTP)

### 3.1 Pola Umum

Setiap Feature test memakai `RefreshDatabase`. Struktur default:

```php
<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NamaModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        // setup role/klasifikasi/factory di sini
    }
}
```

### 3.2 Setup Role & Permission (WAJIB)

Hampir semua modul bergantung pada permission dinamis. **Selalu seed `PermissionSeeder`** di `setUp()` bila test menyentuh akses:

```php
$this->seed(PermissionSeeder::class);

$adminRole = Role::create(['kode' => 'admin', 'label' => 'Administrator']);
$user = User::factory()->create(['role_id' => $adminRole->id]);
$this->actingAs($user);
```

Contoh nyata: `tests/Feature/DocumentSearchServiceTest.php` (seed permission + 3 role).

### 3.3 Setup Klasifikasi & Status

`SuratMasuk` butuh `StatusSurat` minimal 1 (kode `received`), dan `Klasifikasi`:

```php
StatusSurat::create(['kode' => 'received', 'label' => 'Diterima', 'urutan' => 1]);

$normal = Klasifikasi::create(['kode' => 'normal', 'nama' => 'Normal', 'is_rahasia' => false]);
$restricted = Klasifikasi::create(['kode' => 'restricted', 'nama' => 'Restricted', 'is_rahasia' => true]);
```

Referensi: `DocumentSearchServiceTest::setUp()`, `AiAssistantServiceTest::setUp()`.

### 3.4 Test HTTP Request

Pakai `$this->get()`, `post()`, `actingAs()` + assertion Laravel:

```php
$this->actingAs($user)
    ->get(route('dashboard'))
    ->assertOk()
    ->assertSee('Surat Keluar');

$this->post(route('surat-keluar.store'), $payload)
    ->assertRedirect(route('surat-keluar.index'))
    ->assertSessionHas('success');

$this->post(route('...'), $payload)
    ->assertSessionHasErrors(['nomor_surat']);
```

Referensi: `tests/Feature/DashboardSmokeTest.php`.

### 3.5 Seed Database Penuh

Jika modul butuh banyak data default, seed seluruh `DatabaseSeeder`:

```php
protected function setUp(): void
{
    parent::setUp();
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
}
```

Ambil user default via relasi role:

```php
$admin = User::whereHas('role', fn ($q) => $q->where('kode', 'admin'))->first()
    ?? User::factory()->create();
```

Referensi: `DashboardSmokeTest::setUp()`.

### 3.6 Test Akses / Restriksi

Test selalu mencakup skenario **izin** dan **tanpa izin** (terutama surat rahasia/restricted):

```php
// User tanpa permission modul → hasil kosong / redirect / 403
// User dengan akses manual (SuratAkses) → bisa melihat
// Admin → bypass semua
```

Pola di `DocumentSearchServiceTest`:
- `test_user_tanpa_permission_modul_mendapat_hasil_kosong`
- `test_surat_masuk_restricted_muncul_dengan_akses_manual`
- `test_arsip_rahasia_muncul_untuk_admin`

---

## 4. Function / Service Testing

### 4.1 Kapan

Service yang berdiri sendiri (tidak butuh HTTP) tetap ditaruh di `tests/Feature/` selama memakai DB:
- `app/Services/DocumentSearchService.php` → `DocumentSearchServiceTest`
- `app/Services/AiAssistantService.php` → `AiAssistantServiceTest`
- `app/Services/EmailService.php`, `ArsipMediaService`, `AksesKlasifikasiService`, `SuratScopeService`

`tests/Unit/` dipakai untuk fungsi murni tanpa DB/container.

### 4.2 Instansiasi Service

```php
$service = app(DocumentSearchService::class);
$result = $service->search($user, 'surat_keluar', 'keyword');
```

### 4.3 Mock HTTP Eksternal (AI / Email)

Untuk service yang memanggil API eksternal, gunakan `Http::fake()`:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'https://api.x.ai/v1/*' => Http::response(['choices' => [...]], 200),
]);

$result = app(AiAssistantService::class)->ask(...);
```

Referensi: `tests/Feature/AiAssistantServiceTest.php` (set Setting AI di `setUp()` + `Http::fake`).

### 4.4 Mock Setting / Config

Konfigurasi dinamis disimpan di tabel `settings` (key `smtp_*`, `xai_api_key`, dll):

```php
Setting::set('xai_api_key', 'test-key-123');
Setting::set('ai_base_url', 'https://api.x.ai/v1');
Setting::set('ai_model', 'grok-4.3');
```

### 4.5 Assertion Berguna

```php
$this->assertSame(1, $result['found']);
$this->assertCount(2, $result['results']);
$this->assertContains('surat_keluar', array_column($result['results'], 'modul'));
$this->assertSame(route('surat-keluar.show', $surat->uuid), $result['results'][0]['url']);
$this->assertStringContainsString('tidak memiliki akses', $result['note']);
```

---

## 5. Frontend Testing

### 5.1 Status Saat Ini

- **Tidak ada framework unit test JS** (tidak ada Vitest/Jest/Pest di `package.json`).
- JS = modul vanilla di `resources/js/simpel/` (Alpine + Tabler + Chart.js).
- **E2E browser = Laravel Dusk** (sudah terpasang). Dipakai untuk memverifikasi alur login, render halaman, dan interaksi utama.

### 5.2 E2E Browser (Dusk) — Pendekatan Utama untuk Frontend

Gunakan Dusk untuk memverifikasi frontend ketika modul JS menyentuh alur user nyata:

```php
<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NamaModulBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_alur_utama(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                ->visit(route('dashboard'))
                ->assertSee('Surat Keluar')
                ->assertPresent('@tombol-utama'); // pakai data-dusk selector
        });
    }
}
```

Referensi: `tests/Browser/LoginTest.php`.

Aturan Dusk:
- Pakai `DatabaseMigrations` (bukan `RefreshDatabase`) + seed `DatabaseSeeder` di `setUp()`.
- Login via `loginAs()` bila memungkinkan; hindari isi form login berulang.
- Tambahkan atribut `data-dusk` pada elemen yang mau di-*assert* (mis. `@tombol-simpan`), jangan bergantung pada CSS class.
- Jangan pakai Dusk untuk hal yang sudah dijamin test Feature — Dusk untuk alur/browser behavior, bukan logika.
- ChromeDriver jalan di port 9515 (diurus `DuskTestCase::prepare()`). Chrome versi harus kompatibel dengan ChromeDriver terpasang.
- Jangan kirim email asli / panggil API eksternal — sama seperti Feature test.

### 5.3 Manual Test via Blade (tetap relevan)

1. `npm run build` (pastikan aset ter-build) — atau `npm run dev` untuk HMR.
2. Login dengan salah satu akun default (README: `admin@dd.or.id` / `password`, dsb).
3. Buka halaman yang menyentuh modul JS yang diubah:
   - `resources/js/simpel/surat-table.js` → halaman daftar surat
   - `resources/js/simpel/disposisi-form.js` → form disposisi
   - `resources/js/simpel/repeater-lampiran.js` → form lampiran (repeater)
   - `resources/js/simpel/galeri-lightbox.js` → galeri lampiran/foto
   - `resources/js/simpel/tracking-chart.js` → halaman tracking
   - `resources/js/simpel/dashboard2-charts.js` → `/dashboard2`
4. Cek di DevTools console: tidak ada error JS, tidak ada 404 aset, DOM mengikat event.

### 5.4 Checklist Manual (per modul JS)

- [ ] Load halaman tanpa error console.
- [ ] Interaksi utama (klik tombol, submit form) berfungsi.
- [ ] Data dinamis tampil benar (tabel, chart, galeri).
- [ ] Responsif di viewport kecil.
- [ ] Tidak memunculkan akses ke data yang tidak berhak (cek role berbeda).

### 5.5 Menambahkan Vitest (Opsional / saat dibutuhkan)

Jika ada modul JS yang kompleks (utility murni, parsing, kalkulasi) yang layak di-unit-test:

```bash
npm install -D vitest jsdom
```

Tambah script di `package.json`:

```json
"test:js": "vitest run"
```

Contoh test (modul harus mengekspor fungsi murni untuk mudah ditest):

```js
// resources/js/simpel/surat-table.js → ekspor pure function
export function buildSearchUrl(filters) { ... }

// resources/js/__tests__/surat-table.test.js
import { describe, it, expect } from 'vitest';
import { buildSearchUrl } from '../simpel/surat-table.js';

describe('buildSearchUrl', () => {
    it('menggabungkan filter menjadi query string', () => {
        expect(buildSearchUrl({ q: 'surat', status: 'received' })).toBe('?q=surat&status=received');
    });
});
```

Aturan:
- **Hanya test fungsi murni** (tanpa DOM global) kecuali sudah ada jsdom environment.
- Jangan menulis test yang bergantung pada Tabler/Alpine internal.
- Ekspor fungsi yang mau ditest (jangan hanya IIFE).

---

## 6. Konvensi & Checklist Penulisan Test

### 6.1 Naming

- Nama class: `NamaModulModuleTest`, `NamaServiceTest`, `NamaModulRestrictedAccessTest`.
- Nama method test: `snake_case` deskriptif (Indonesia), prefix `test_`.

### 6.2 Aturan

- Setiap Feature test pakai `RefreshDatabase` (kecuali alasan kuat). Browser/Dusk test pakai `DatabaseMigrations`.
- Seed `PermissionSeeder` saat test menyentuh akses/role.
- Selalu test skenario sukses **dan** gagal/tanpa izin.
- Jangan pernah hit API eksternal asli — pakai `Http::fake()`.
- Jangan hardcode ID — ambil via relasi/query (`->first()`, `->value('id')`).
- Jangan kirim email asli — test pakai `MAIL_MAILER=array`.
- Dusk hanya untuk alur/browser behavior (login, render, interaksi user) — logika tetap di Feature test.
- Dusk wajib pakai seed `DatabaseSeeder` dan `data-dusk` selector (bukan CSS class).
- Untuk kode baru (fitur/modul), **wajib** ada minimal: 1 test akses + 1 test CRUD/alur utama + 1 test edge case. Bila modul menyentuh alur user nyata (login/form), tambah 1 Dusk test.

### 6.3 Sebelum Selesai

1. `composer test` — semua hijau.
2. `npm run build` — aset frontend tidak error.
3. Manual smoke test halaman terkait perubahan.
4. Bila ada perubahan UI/alur user: `php artisan dusk` — semua hijau.

---

## 7. Referensi Test Existing

| File Test | Melindungi |
|---|---|
| `tests/Feature/DocumentSearchServiceTest.php` | Service pencarian + akses klasifikasi rahasia |
| `tests/Feature/AiAssistantServiceTest.php` | AI assistant service + `Http::fake()` |
| `tests/Feature/AiAssistantEndpointTest.php` | Endpoint HTTP AI assistant |
| `tests/Feature/DashboardSmokeTest.php` | Render dashboard + seed penuh |
| `tests/Feature/SuratKeputusanModuleTest.php` | CRUD modul arsip SK |
| `tests/Feature/NotulensiRapimModuleTest.php` | CRUD modul notulensi |
| `tests/Feature/KomitePersetujuanModuleTest.php` | CRUD modul komite |
| `tests/Feature/SuratMasukRestrictedAccessTest.php` | Restriksi akses surat masuk |
| `tests/Feature/DisposisiAutoCreateUserTest.php` | Auto-create user pada disposisi |
| `tests/Feature/GoogleLoginTest.php` | Login Google (Socialite) |
| `tests/Feature/Auth/*` | Breeze default (auth) |
| `tests/Browser/LoginTest.php` | E2E login sukses & password salah (Dusk) |
