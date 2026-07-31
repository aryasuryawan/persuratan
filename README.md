# SiMPEL — Sistem Manajemen Surat Elektronik

Aplikasi manajemen surat masuk & surat keluar berbasis web untuk Yayasan Dompet Dhuafa Republika. Dibangun dengan **Laravel 13** dan **Tabler UI**.

## Fitur

- **Penerimaan Surat Masuk** — pencatatan surat, upload lampiran (PDF/JPG/PNG), klasifikasi & sifat surat
- **Verifikasi Surat** — alur verifikasi oleh sekretaris sebelum diteruskan
- **Disposisi** — disposisi ke unit pelaksana atau langsung pimpinan, catatan pimpinan, batas waktu, upload lampiran, dan proteksi submit ganda
- **Follow Up Disposisi** — tindak lanjut berjenjang dari pelaksana dengan lampiran
- **Cetak Lembar Disposisi** — layout standar siap cetak (kop lembaga dari pengaturan, kolom tanda tangan)
- **Riwayat Status & Timeline** — lacak pergerakan surat lengkap dengan siapa/kepada siapa dan deadline
- **Surat Keluar** — pencatatan dengan kode surat master, keterkaitan ke surat masuk
- **Tracking** — pelacakan surat dengan grafik tren
- **Dashboard** — statistik surat per status, tren masuk/keluar (Chart.js & ECharts)
- **Master Data** — direktorat, divisi, departemen, klasifikasi, kode surat, role, pengguna, sekretaris-pimpinan
- **Pengaturan** — nama aplikasi/lembaga, logo, favicon, alamat, kontak, footer
- **Manajemen Pengguna** — multi role, penempatan unit (direktorat/divisi/departemen), jabatan
- **Level Akses Klasifikasi** — kontrol akses berdasarkan klasifikasi & sifat surat

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
  Services/           # AksesKlasifikasiService, SuratScopeService
database/
  migrations/         # Skema database
  seeders/            # Seeder data awal & contoh
resources/views/
  penerimaan-surat/   # Detail surat + cetak lembar disposisi
  disposisi/          # Daftar surat untuk disposisi
  surat-keluar/       # CRUD surat keluar
  master/             # Master data & pengaturan
routes/web.php        # Definisi route
```

## Lisensi

Proyek dikembangkan untuk keperluan internal Yayasan Dompet Dhuafa Republika.
