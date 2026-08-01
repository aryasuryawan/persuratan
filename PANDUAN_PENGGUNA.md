# Panduan Pengguna SiMPEL

Sistem Manajemen Surat Elektronik — Yayasan Dompet Dhuafa Republika

---

## Daftar Isi

1. [Masuk & Keluar](#1-masuk--keluar)
2. [Dashboard](#2-dashboard)
3. [Surat Masuk](#3-surat-masuk)
4. [Verifikasi Surat](#4-verifikasi-surat)
5. [Disposisi](#5-disposisi)
6. [Follow Up Disposisi](#6-follow-up-disposisi)
7. [Cetak Lembar Disposisi](#7-cetak-lembar-disposisi)
7. [Riwayat & Timeline](#7-riwayat--timeline)
8. [Surat Keluar](#8-surat-keluar)
9. [Tracking](#9-tracking)
10. [Arsip Lainnya](#10-arsip-lainnya)
11. [Pengaturan & Master Data](#11-pengaturan--master-data)
    - [11.1 Pengaturan Email / SMTP (Notifikasi)](#111-pengaturan-email--smtp-notifikasi)
    - [11.2 Log Pengiriman Email](#112-log-pengiriman-email)
12. [Manajemen Pengguna](#12-manajemen-pengguna)
13. [Login Google](#13-login-google)
14. [Tips & Troubleshooting](#14-tips--troubleshooting)

---

## 1. Masuk & Keluar

### Login
1. Buka URL aplikasi (mis. `https://esurat2.test` atau `http://localhost`)
2. Isi **Email** dan **Password**
3. Centang "Ingat saya di perangkat ini" jika ingin session bertahan
4. Klik **Login**

### Login dengan Google (opsional)
- Jika admin sudah mengaktifkan, tombol **"Login dengan Google"** muncul di halaman login
- Klik tombol → pilih akun Google → otomatis masuk jika email terdaftar di sistem
- Syarat: email Google **harus sama** dengan email user di sistem, dan email Google sudah terverifikasi

### Keluar
- Klik avatar/nama di pojok kanan atas → **Keluar**

---

## 2. Dashboard

Setelah login, Anda diarahkan ke Dashboard. Tampilkan:
- **Ringkasan statistik**: jumlah surat per status (Diterima, Diverifikasi, Didisposisikan, dll)
- **Grafik tren** surat masuk/keluar per bulan (Chart.js & ECharts)
- **Aksi cepat**: tombol ke halaman utama setiap modul

---

## 3. Surat Masuk

### Daftar Surat
- Menu **Penerimaan Surat** → **Daftar Surat**
- Filter: pencarian teks, filter status, filter klasifikasi, rentang tanggal
- Kolom: Nomor Agenda, Tanggal Diterima, Nomor Surat, Pengirim, Perihal, Sifat, Status, Aksi

### Tambah Surat Baru
1. Klik **Tambah Surat** (tombol biru kanan atas)
2. Isi form:
   - **Nomor Agenda** (otomatis terisi, bisa diubah)
   - **Nomor Surat** (dari pengirim)
   - **Tanggal Surat** & **Tanggal Diterima**
   - **Pengirim** (instansi/pengirim)
   - **Klasifikasi** (Rahasia, Biasa, Penting, Segera)
   - **Sifat** (Rahasia, Biasa, Penting, Segera)
   - **Perihal**
   - **Catatan** (opsional)
   - **Lampiran**: klik area drag-drop atau pilih file (PDF, JPG, PNG, maks 10MB per file, multiple)
3. Klik **Simpan** → surat masuk dengan status **Diterima**

### Detail Surat
- Klik baris surat di daftar → halaman detail
- Tampil: metadata lengkap, lampiran (klik untuk download/pratinjau), daftar disposisi & follow up, riwayat status (timeline), riwayat perubahan data

---

## 4. Verifikasi Surat

*Hanya untuk role **Sekretaris** & **Admin***

1. Buka detail surat dengan status **Diterima**
2. Klik tombol **Verifikasi** (kanan atas)
3. Konfirmasi → status berubah ke **Diverifikasi** → otomatis masuk antrian ke Pimpinan

---

## 5. Disposisi

### Membuat Disposisi Baru
1. Di detail surat (status minimal **Diverifikasi**), klik tombol **Disposisi** (sidebar kanan)
2. Modal terbuka, pilih **Jenis Disposisi**:
   - **Ke Pelaksana** → pilih **Pelaksana** (user role unit_lain) + catatan + deadline
   - **Tindak Lanjut Langsung** → catatan pimpinan (tanpa pelaksana)
3. Centang **Instruksi** (bisa multiple):
   - Diperiksa
   - Diperhatikan
   - Ditindaklanjuti
   - Dilaksanakan
   - Arsip/Ditolak
4. *(Opsional)* **Atas Nama Pimpinan** (hanya sekretaris): pilih pimpinan yang diwakili
5. Upload **Lampiran** (opsional)
6. Klik **Simpan Disposisi**

> Proteksi submit ganda: tombol Simpan hanya bisa diklik sekali per buka modal.

> **Notifikasi Email:** Saat disposisi disimpan, sistem otomatis mengirim email notifikasi ke setiap penerima.
> - **Penerima user aplikasi** → email diambil otomatis dari data user (tidak perlu diisi).
> - **Penerima nama/jabatan bebas (freetext)** → **wajib isi email** di kolom yang tersedia agar notifikasi terkirim. Tanpa email, nama tsb tetap tercatat sebagai penerima tapi email tidak dikirim.
> - Log pengiriman bisa dicek di **History Surat → tab Notifikasi Email** atau **Pengaturan → Log Pengiriman Email**.
> - Email hanya terkirim jika admin sudah mengaktifkan & mengonfigurasi SMTP (lihat §11.1).

### Edit Disposisi
- Di daftar disposisi (detail surat), klik **ikon pensil** (edit) pada baris disposisi
- Form edit inline terbuka: ubah Instruksi, Catatan, Deadline
- Klik **Simpan**

### Hapus Disposisi
- Klik **ikon tong sampah** (hapus) pada baris disposisi
- Konfirmasi → disposisi terhapus
- Jika tidak ada disposisi lain, status surat otomatis kembali ke **Diterima**

### Siapa Bisa Disposisi?
| Role | Bisa Buat Disposisi | Bisa Edit/Hapus |
|---|---|---|
| Sekretaris | Ya (semua) | Ya (semua) |
| Admin | Ya (semua) | Ya (semua) |
| Pimpinan | Hanya "Tindak Lanjut Langsung" | Hanya milik sendiri |
| Unit Lain | Tidak | Tidak |

---

## 6. Follow Up Disposisi

*Hanya untuk **Pelaksana** (user yang didisposisikan) dan Sekretaris/Admin*

1. Di detail surat, cari baris disposisi yang ditujukan ke Anda
2. Klik **Follow Up** (ikon panah/tindak lanjut) pada baris tersebut
2. Isi **Keterangan** hasil tindak lanjut
3. Upload **Lampiran** hasil kerja (opsional)
3. Klik **Simpan Follow Up**
4. Bisa menambah follow up berulang (berjenjang)

---

## 7. Cetak Lembar Disposisi

1. Di detail surat, klik tombol **Cetak Lembar Disposisi** (sidebar / tombol print)
2. Halaman cetak terbuka (layout A4, kop organisasi dari Pengaturan)
3. Isi otomatis:
   - **Nomor Agenda**, **Tanggal Diterima**, **Pengirim**, **Perihal**
   - **Instruksi** (dipisah koma, mis. "Ditindaklanjuti, Dilaksanakan")
   - **Pelaksana** (nama + jabatan)
   - **Batas Waktu** (di kotak MEMO)
   - **Memo/Isi Disposisi**
4. Tekan `Ctrl+P` / klik tombol **Cetak** → pilih printer / Save as PDF

---

## 8. Riwayat & Timeline

Di detail surat (bagian bawah), klik **History Surat** untuk expand:

### Tab Status
- Timeline visual: setiap perubahan status (Diterima → Diverifikasi → Didisposisikan → dll)
- Tampil: status, siapa mengubah, kapan, catatan, ke siapa disposisi, deadline

### Tab Perubahan Data
- Log perubahan field: Instruksi, Pelaksana, Catatan, Deadline
- Format: **Field: nilai lama → nilai baru**, siapa mengubah, kapan

### Tab Notifikasi Email
- Timeline pengiriman email notifikasi disposisi untuk surat ini
- Tampil: nama & alamat email penerima, subjek, status (**Terkirim**/**Gagal**), penyedia email, waktu
- Jika gagal: pesan error tampil (mis. konfigurasi SMTP salah, email penerima tidak valid)

---

## 8. Surat Keluar

*Menu berada di dropdown **Arsip Lainnya** → **Surat Keluar***

### Daftar & Tambah
- Sama seperti Surat Masuk, tapi field: **Kode Surat** (master), **Tujuan**, **Terkait Surat Masuk** (opsional, link ke surat masuk)
- Status: Draft → Dikirim → Diarsipkan

---

## 9. Tracking

Menu **Tracking** → grafik & tabel pelacakan:
- Filter: rentang tanggal, klasifikasi, status
- Grafik tren jumlah surat per periode
- Tabel detail dengan export

---

## 10. Arsip Lainnya

Menu dropdown **Arsip Lainnya** (ikon arsip di navbar):

### 10.1 Surat Keputusan
- Metadata: Nomor SK, Perihal, Kategori, Tanggal SK, Tanggal Berlaku, Ditetapkan Oleh, Ringkasan, Status (Aktif/Tidak Berlaku)
- Lampiran + Foto Kegiatan (drag-drop, multiple)
- Tanpa workflow approval — simpan langsung

### 10.2 Hasil Komite Persetujuan
- Metadata: Nomor, Perihal, Tanggal, Anggota Komite, Hasil (Disetujui/Ditolak/Dengan Catatan), Catatan
- Lampiran + Foto

### 10.3 Notulensi Rapim
- Metadata: Judul Rapat, Tanggal, Tempat, Peserta, Notulis, Ringkasan Pembahasan
- Lampiran + Foto

> Semua modul arsip: **hanya administrasi/pengarsipan**, tidak ada workflow approval. Role `sekretaris` & `admin` bisa buat/edit/hapus; `pimpinan` & `unit_lain` read-only.

---

## 11. Pengaturan & Master Data

*Akses: **Admin** saja*

### Pengaturan Aplikasi
- Menu **Master Data** → **Pengaturan**
- Nama Aplikasi, Nama Lembaga, Logo, Favicon, Alamat, Telepon, Email, Website, Footer

### 11.1 Pengaturan Email / SMTP (Notifikasi)

*Akses: **Admin** saja. Menu **Master Data** → **Pengaturan** → tab **Email / SMTP**.*

1. Centang **"Aktifkan Email Notifikasi"**.
2. Pilih **Driver / Penyedia Email**:
   | Provider | Yang Diisi |
   |---|---|
   | **SMTP Standar / PHP Mailer** | Host, Port, Username, Password, Enkripsi |
   | **MailerSend** | API Key (`mlsn.…`); host otomatis `smtp.mailersend.net` |
   | **SendGrid** | API Key (`SG.…`); host otomatis `smtp.sendgrid.net` |
   | **Google Mail (Gmail/Workspace)** | Email + **App Password** (bukan password biasa); host otomatis `smtp.gmail.com` |
3. Isi **Dari Alamat** & **Dari Nama** (identitas pengirim).
4. Klik **Simpan Pengaturan Email**.
5. Klik **Test Koneksi & Kirim Email Uji** untuk memastikan konfigurasi benar (email uji dikirim ke alamat username/from).

> **Catatan Gmail:** Aktifkan 2-Step Verification di akun Google → buat **App Password** (Google Account → Security → App Passwords) → gunakan 16 karakter app password tsb sebagai password SMTP. Untuk akun Workspace, izinkan "Less secure app" atau gunakan SMTP relay bila ada kendala.

### 11.2 Log Pengiriman Email

- Menu **Master Data** → **Pengaturan** → tab **Log Pengiriman Email**
- Menampilkan seluruh riwayat pengiriman: waktu, jenis (Disposisi), penerima, email, subjek, status (Terkirim/Gagal/Pending)
- Log gagal menampilkan pesan error (hover/klik tombol **Error**)

### Master Data
| Menu | Fungsi |
|---|---|
| **Direktorat** | Unit organisasi tingkat 1 |
| **Divisi** | Unit organisasi tingkat 2 (di bawah direktorat) |
| **Departemen** | Unit organisasi tingkat 3 |
| **Klasifikasi** | Kategori surat (Rahasia, Biasa, Penting, Segera, dll) |
| **Kode Surat** | Master kode surat keluar (mis. SK, SM, ST) |
| **Status Surat** | Master status alur surat |
| **Role** | Peran pengguna (admin, sekretaris, pimpinan, dll) |

---

## 12. Manajemen Pengguna

*Akses: **Admin** saja*

Menu **Master Data** → **Pengguna**:

### Tambah User
1. Klik **Tambah Pengguna**
2. Isi: Nama, Email, Password, **Role**, **Jabatan** (opsional), **Unit** (Direktorat/Divisi/Departemen)
3. **Aktif** dicentang → user bisa login
3. Simpan

### Kolom Jabatan
- Digunakan untuk **Cetak Lembar Disposisi**: nama jabatan tampil di samping nama pelaksana
- Contoh jabatan YDDR: *Wakil Ketua Pengurus*, *Sekretaris Pengurus*, *Bendahara Pengurus*, *Kepala Bidang*, dll

### Sekretaris-Pimpinan (Proxy)
- Menu **Master Data** → **Sekretaris-Pimpinan**
- Hubungkan sekretaris ke pimpinan yang dilayani
- Saat disposisi, sekretaris pilih **Atas Nama Pimpinan** → disposisi tercatat atas nama pimpinan tsb

---

## 13. Login Google

### Prasyarat (Admin)
1. Google Cloud Console → project → OAuth consent screen (External) → Test users: tambah email user yang diizinkan
2. Credentials → OAuth Client ID (Web) → Authorized redirect URI:
   - Local: `http://localhost/auth/google/callback`
   - Production: `https://domain.com/auth/google/callback`
3. Copy **Client ID** & **Client Secret** ke `.env`:
   ```env
   GOOGLE_CLIENT_ID=xxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=xxx
   GOOGLE_REDIRECT_URI=https://domain.com/auth/google/callback
   GOOGLE_ALLOWED_DOMAINS=dompetdhuafa.org
   ```
4. `php artisan config:clear`

### Pengguna
- Buka halaman login → klik **Login dengan Google**
- Pilih akun Google → jika email cocok user di sistem & email verified → langsung masuk
- Jika email tidak terdaftar → pesan "Akun Google ini belum terdaftar di sistem, hubungi admin"
- Jika user `is_active = false` → ditolak "Akun Anda dinonaktifkan"

> **Catatan**: Google hanya menerima `http://` untuk redirect URI `localhost`. Untuk domain lain wajib `https://`.

---

## 14. Tips & Troubleshooting

| Masalah | Solusi |
|---|---|
| Tombol Login Google tidak muncul | Pastikan `GOOGLE_CLIENT_ID` diisi di `.env` lalu `php artisan config:clear` |
| Error "redirect_uri_mismatch" | Pastikan `GOOGLE_REDIRECT_URI` di `.env` **sama persis** dengan Authorized redirect URI di Google Cloud Console (termasuk protokol http/https) |
| File lampiran tidak upload | Cek ukuran (maks 10MB/file), tipe file (pdf/jpg/jpeg/png), permission `storage/app/public` writable |
| Tab "Perubahan Data" tidak bisa diklik | Hard refresh browser (`Ctrl+Shift+R`) — script `switchHistTab` perlu load ulang |
| Disposisi tidak tersimpan | Pastikan pilih **Jenis Disposisi** dulu (radio), baru isi field lain. Instruksi minimal 1 centang. |
| Email notifikasi tidak terkirim | Cek **Pengaturan → Email/SMTP**: apakah "Aktifkan Email Notifikasi" dicentang & config SMTP benar. Gunakan **Test Koneksi**. Cek tab **Log Pengiriman Email** untuk pesan error. |
| Email uji gagal "Authentication failed" | Username/password SMTP salah. Gmail: gunakan **App Password** bukan password biasa. SendGrid: username `apikey`. MailerSend: API key sebagai username & password. |
| Email uji gagal "Connection could not be established" | Salah host/port. Verifikasi host (mis. `smtp.gmail.com`), port (587 TLS / 465 SSL), dan pastikan server boleh keluar port SMTP (firewall/hosting). |
| Email masuk ke spam | Tambahkan SPF/DKIM/DMARC dari penyedia (MailerSend, SendGrid, Google Workspace) untuk domain pengirim. |
| Penerima freetext tidak dapat email | Kolom email penerima kosong saat membuat disposisi — isi email agar terkirim. |
| Cetak disposisi kosong | Buat disposisi baru setelah update terbaru (data lama kolom `instruksi`/`pelaksana` kosong) |
| Error 500 saat migrasi | Cek `php artisan migrate:status`, pastikan DB connection benar, cek `php artisan migrate` manual |

---

## Pintasan Keyboard

| Tindakan | Shortcut |
|---|---|
| Cetak halaman | `Ctrl + P` |
| Refresh halaman | `Ctrl + R` / `F5` |
| Hard refresh (bersihkan cache JS) | `Ctrl + Shift + R` |
| Buka pencarian browser | `Ctrl + F` |

---

## Kontak Dukungan

- **Email**: corporatesecretary@dompetdhuafa.org / secretary@dompetdhuafa.org
- **Catatan**: Kirim resume hasil pertemuan maksimal H+3 setelah kegiatan

---

*Dokumen ini diperbarui seiring pengembangan aplikasi. Versi terbaru selalu di repositori Git.*