# SiMPEL — Sistem Manajemen Surat Elektronik

Aplikasi manajemen surat masuk & surat keluar berbasis web untuk Yayasan Dompet Dhuafa Republika. Dibangun dengan **Laravel 13** dan **Tabler UI**.

## Fitur

- **Penerimaan Surat Masuk** — pencatatan surat, upload lampiran (PDF/JPG/PNG), klasifikasi & sifat surat
- **Verifikasi Surat** — alur verifikasi oleh sekretaris sebelum diteruskan
- **Disposisi** — disposisi ke unit pelaksana atau langsung pimpinan, catatan pimpinan, batas waktu, upload lampiran, proteksi submit ganda, **edit inline**, **hapus** (sekretaris/admin/pembuat), **cetak lembar disposisi** dengan Instruksi, Pelaksana, Batas waktu
- **Follow Up Disposisi** — tindak lanjut berjenjang dari pelaksana dengan lampiran
- **Riwayat Status & Timeline** — lacak pergerakan surat lengkap dengan siapa/kepada siapa, deadline, dan **tab Perubahan Data** (instruksi, pelaksana, catatan, deadline)
- **Surat Keluar** — pencatatan dengan kode surat master, keterkaitan ke surat masuk (masuk ke menu **Arsip Lainnya**)
- **Tracking** — pelacakan surat dengan grafik tren
- **Dashboard** — statistik surat per status, tren masuk/keluar (Chart.js & ECharts)
- **Master Data** — direktorat, divisi, departemen, klasifikasi, kode surat, role, pengguna, sekretaris-pimpinan
- **Pengaturan** — nama aplikasi/lembaga, logo, favicon, alamat, kontak, footer
- **Manajemen Pengguna** — multi role, penempatan unit (direktorat/divisi/departemen), jabatan
- **Level Akses Klasifikasi** — kontrol akses berdasarkan klasifikasi & sifat surat
- **Modul Arsip Lainnya** — Surat Keputusan, Hasil Komite Persetujuan, Notulensi Rapim (metadata + lampiran + foto kegiatan, tanpa workflow approval)
- **Login Google (Socialite)** — tombol "Login dengan Google" di halaman login, **hanya user existing** (email cocok), validasi email_verified Google, domain allowlist via `GOOGLE_ALLOWED_DOMAINS`

## Teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | PHP 8.3, Laravel 13 |
| Frontend | Blade, Tabler UI, Alpine.js |
| Grafik | Chart.js, ECharts |
| Build | Vite, Tailwind CSS |
| Database | MySQL / MariaDB |

## Persyaratan

- PHP >= 8.3
- Composer
- Node.js & npm
- MySQL / MariaDB

## Instalasi

```bash
# 1. Clone / salin project, lalu install dependency
composer install
npm install

# 2. Konfigurasi environment
cp .env.example .env
php artisan key:generate

# 3. Atur koneksi database di .env
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=esurat2
#    DB_USERNAME=root
#    DB_PASSWORD=

# 4. Migrasi & seed
php artisan migrate --seed

# 5. Build aset frontend
npm run build

# 6. Jalankan
php artisan serve
# atau `composer dev` untuk dev server + queue + vite
```

## Google Login Setup

1. Google Cloud Console → OAuth consent screen (External) → Test users (tambah email tester)
2. Credentials → OAuth client ID (Web) → Authorized redirect URI:
   - **Local**: `http://localhost/auth/google/callback` (set `APP_URL=http://localhost`)
   - **Production**: `https://domain.com/auth/google/callback` (wajib HTTPS)
3. Isi `.env`:
   ```
   GOOGLE_CLIENT_ID=xxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=xxx
   GOOGLE_REDIRECT_URI=https://domain.com/auth/google/callback
   GOOGLE_ALLOWED_DOMAINS=dompetdhuafa.org,gmail.com
   ```
4. `php artisan config:clear`
5. Tombol "Login dengan Google" muncul di halaman login (hanya jika `GOOGLE_CLIENT_ID` diisi)

## Akun Default (Seeder)

| Role | Email | Password |
|---|---|---|
| Administrator | `admin@dd.or.id` | `password` |
| Sekretaris | `sekretaris@dd.or.id` | `password` |
| Pimpinan | `pimpinan@dd.or.id` | `password` |

> Seeder `PreservedDataSeeder` & `TestDataSeeder` menghasilkan data contoh dan pengguna tambahan (pakai `updateOrCreate` sehingga data lama dipertahankan). Untuk produksi, nonaktifkan kedua seeder tersebut di `database/seeders/DatabaseSeeder.php`.

## Fresh Seed (Reset Database)

Hapus semua tabel & data lama, lalu buat ulang + isi seed dari nol:

```bash
php artisan migrate:fresh --seed
```

Perintah ini menghapus seluruh data, menjalankan semua migrasi baru, dan mengisi data awal (role, status, klasifikasi, user default, master data, data contoh). Gunakan saat pengembangan awal atau ingin mulai dari kondisi bersih.

Untuk seed ulang **tanpa** menghapus database (hanya isi ulang data awal):

```bash
php artisan db:seed
```

> ⚠️ `migrate:fresh` menghapus semua data termasuk file lampiran yang tercatat di database. Backup `storage/app/public` jika ingin menyimpan file-nya.

## Struktur Alur Surat Masuk

```
Diterima → Diverifikasi → Ke Pimpinan → Didisposisikan → Diproses → Selesai → Diarsipkan
```

## Role

| Role | Deskripsi |
|---|---|
| `admin` | Kelola master data, pengguna, pengaturan |
| `sekretaris` | Catat surat, verifikasi, disposisi |
| `pimpinan` | Terima disposisi langsung, tindak lanjut |
| `unit_lain` | Pelaksana/tujuan disposisi, follow up |
| `cc_security` | Akses tembusan / keamanan |

## Struktur Direktori Utama

```
app/
  Actions/            # Logika aksi (BuatDisposisi, dll)
  Events/             # Event perubahan status surat
  Http/Controllers/   # Controller (termasuk Master/)
  Listeners/          # Listener pencatatan riwayat
  Models/             # Model Eloquent
  Services/           # ArsipMediaService, AksesKlasifikasiService, SuratScopeService
database/
  migrations/         # Skema database
  seeders/            # Seeder data awal & contoh
resources/views/
  penerimaan-surat/   # Detail surat + cetak lembar disposisi
  disposisi/          # Daftar surat untuk disposisi
  surat-keluar/       # CRUD surat keluar
  surat-keputusan/    # Modul Surat Keputusan (arsip)
  komite-persetujuan/ # Modul Hasil Komite Persetujuan (arsip)
  notulensi-rapim/    # Modul Notulensi Rapim (arsip)
  master/             # Master data & pengaturan
  arsip/partials/     # Partials lampiran, foto, galeri
routes/web.php        # Definisi route
```

## Lisensi

Proyek dikembangkan untuk keperluan internal Yayasan Dompet Dhuafa Republika.