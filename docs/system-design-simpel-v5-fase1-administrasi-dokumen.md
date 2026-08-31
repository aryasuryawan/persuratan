# Tambahan System Design — SiMPEL v5
## Fase Awal: Administrasi Surat Keputusan, Hasil Komite Persetujuan, dan Notulensi Rapat Pimpinan

> Dokumen ini adalah **versi ruang lingkup lebih kecil** dari `system-design-simpel-v5-tambahan-sk-notulensi-komite.md`. Fase awal ini fokus pada **administrasi/pengarsipan** — mencatat dan menyimpan tiga jenis dokumen beserta lampiran dan foto kegiatannya — **bukan** workflow persetujuan berjenjang, voting, atau penomoran otomatis. Proses persetujuan/penandatanganan tetap berjalan di luar sistem (manual/fisik); sistem ini adalah tempat arsip digital resminya. Modul workflow penuh (§9–§11 di dokumen sebelumnya) tetap relevan sebagai **roadmap fase berikutnya**.

---

## 15. Ruang Lingkup Fase Awal

| Modul | Yang Dicatat | Yang **Tidak** Ada di Fase Awal |
|---|---|---|
| **Surat Keputusan** | Metadata SK yang sudah terbit + file SK (PDF/scan) + foto kegiatan terkait (jika ada) | Alur draft→review→tandatangani, penomoran otomatis, multi-penandatangan |
| **Hasil Komite Persetujuan** | Catatan hasil rapat komite yang sudah diputuskan + dokumen pendukung + foto kegiatan rapat | Pengajuan proposal digital, penjadwalan rapat, voting per anggota |
| **Notulensi Rapat Pimpinan** | Ringkasan rapim, keputusan/arahan pimpinan + dokumen pendukung + foto kegiatan rapat | Undangan digital, tracking kehadiran, dashboard tindak lanjut otomatis |

Ketiganya punya kebutuhan yang sama: **metadata singkat + lampiran dokumen + galeri foto kegiatan**. Karena itu, lampiran dan foto dibuat sebagai tabel **generik (polymorphic)** yang dipakai bersama oleh ketiga modul — tidak perlu duplikasi struktur.

---

## 16. Skema Database

### 16.1 Tabel Inti per Modul

```php
Schema::create('surat_keputusan', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('nomor_sk');                 // diisi manual, sesuai nomor SK fisik yang sudah terbit
    $table->string('perihal');
    $table->string('kategori')->nullable();      // teks bebas/select sederhana: Kepegawaian, Organisasi, Program, dst.
    $table->date('tanggal_sk');
    $table->date('tanggal_berlaku')->nullable();
    $table->string('ditetapkan_oleh')->nullable(); // nama + jabatan pimpinan penandatangan, teks bebas
    $table->text('ringkasan')->nullable();        // ringkasan isi SK, bukan diktum lengkap
    $table->string('status', 20)->default('aktif'); // aktif, tidak_berlaku
    $table->foreignId('dicatat_oleh_id')->constrained('users');
    $table->timestamps();
});

Schema::create('komite_persetujuan', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('judul');
    $table->string('jenis', 20);                 // pembelian, prinsip_proyek
    $table->date('tanggal_rapat');
    $table->text('daftar_peserta')->nullable();   // teks bebas, satu nama per baris
    $table->decimal('nilai_pembelian', 18, 2)->nullable(); // khusus jenis pembelian
    $table->string('hasil', 20);                  // disetujui, ditolak, revisi, ditunda
    $table->text('catatan_keputusan')->nullable();
    $table->foreignId('dicatat_oleh_id')->constrained('users');
    $table->timestamps();
});

Schema::create('notulensi_rapim', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('judul_rapat');
    $table->date('tanggal');
    $table->text('daftar_peserta')->nullable();
    $table->text('ringkasan_pembahasan');
    $table->text('keputusan_arahan')->nullable(); // keputusan/arahan pimpinan
    $table->foreignId('dicatat_oleh_id')->constrained('users');
    $table->timestamps();
});
```

Field `status` di ketiga tabel sengaja dibuat sederhana — tidak ada alur multi-tahap. Setiap record begitu disimpan sudah dianggap arsip resmi (opsional: tambahkan satu flag `terkunci` yang dikunci oleh Admin/Sekretaris agar tidak bisa diedit sembarangan setelah diverifikasi, tanpa perlu state machine penuh).

### 16.2 Tabel Generik: Lampiran & Foto Kegiatan

```php
Schema::create('lampiran', function (Blueprint $table) {
    $table->id();
    $table->morphs('lampirkan');          // lampirkan_type, lampirkan_id — bisa ke SuratKeputusan, KomitePersetujuan, NotulensiRapim
    $table->string('nama_file');
    $table->string('path');
    $table->string('tipe_file', 10)->nullable();   // pdf, docx, xlsx, jpg, dst.
    $table->unsignedBigInteger('ukuran_bytes')->nullable();
    $table->string('keterangan')->nullable();       // mis. "SK Asli (scan)", "Daftar Hadir", "RAB Pembelian"
    $table->foreignId('diunggah_oleh_id')->constrained('users');
    $table->timestamps();
});

Schema::create('foto_kegiatan', function (Blueprint $table) {
    $table->id();
    $table->morphs('kegiatan');            // kegiatan_type, kegiatan_id — sama tiga model di atas
    $table->string('path');
    $table->string('caption')->nullable();
    $table->unsignedSmallInteger('urutan')->default(0); // urutan tampil di galeri
    $table->foreignId('diunggah_oleh_id')->constrained('users');
    $table->timestamps();
});
```

Dipisah dari `lampiran` supaya foto kegiatan bisa ditampilkan sebagai **galeri** (grid + lightbox) terpisah dari daftar dokumen yang sifatnya **untuk diunduh**.

### 16.3 Model & Trait

```php
// app/Concerns/PunyaLampiran.php
trait PunyaLampiran
{
    public function lampiran()
    {
        return $this->morphMany(Lampiran::class, 'lampirkan');
    }
}

// app/Concerns/PunyaFotoKegiatan.php
trait PunyaFotoKegiatan
{
    public function fotoKegiatan()
    {
        return $this->morphMany(FotoKegiatan::class, 'kegiatan')->orderBy('urutan');
    }
}
```

```php
class SuratKeputusan extends Model
{
    use PunyaLampiran, PunyaFotoKegiatan;

    protected $casts = ['tanggal_sk' => 'date', 'tanggal_berlaku' => 'date'];

    public function dicatatOleh() { return $this->belongsTo(User::class, 'dicatat_oleh_id'); }
}

class KomitePersetujuan extends Model
{
    use PunyaLampiran, PunyaFotoKegiatan;

    protected $casts = ['tanggal_rapat' => 'date'];

    public function dicatatOleh() { return $this->belongsTo(User::class, 'dicatat_oleh_id'); }
}

class NotulensiRapim extends Model
{
    use PunyaLampiran, PunyaFotoKegiatan;

    protected $casts = ['tanggal' => 'date'];

    public function dicatatOleh() { return $this->belongsTo(User::class, 'dicatat_oleh_id'); }
}
```

`Lampiran` dan `FotoKegiatan` sendiri cukup model polos tanpa relasi tambahan selain `morphTo()` dan `belongsTo(User::class, 'diunggah_oleh_id')`.

---

## 17. UI Tabler

### 17.1 Form Input (pola sama untuk ketiga modul)

Contoh untuk Notulensi Rapim — form lainnya mengikuti pola yang sama (metadata di atas, upload lampiran & foto di bawah):

```blade
{{-- resources/views/notulensi-rapim/form.blade.php --}}
@extends('layouts.app')
@section('page-title', 'Notulensi Rapat Pimpinan')

@section('content')
<form method="POST" action="{{ route('notulensi-rapim.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Informasi Rapat</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label required">Judul Rapat</label>
                    <input type="text" name="judul_rapat" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Daftar Peserta</label>
                <textarea name="daftar_peserta" class="form-control" rows="3" placeholder="Satu nama per baris"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label required">Ringkasan Pembahasan</label>
                <textarea name="ringkasan_pembahasan" id="editor-ringkasan" class="form-control" rows="6"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Keputusan / Arahan Pimpinan</label>
                <textarea name="keputusan_arahan" class="form-control" rows="4"></textarea>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Lampiran Dokumen</h3></div>
        <div class="card-body">
            <div class="mb-2" id="repeater-lampiran">
                {{-- baris: input file + keterangan, ditambah via JS --}}
                <div class="row g-2 align-items-center mb-2 baris-lampiran">
                    <div class="col-md-7">
                        <input type="file" name="lampiran[]" class="form-control">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="lampiran_keterangan[]" class="form-control" placeholder="Keterangan (mis. Daftar Hadir)">
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-lampiran">+ Tambah Lampiran</button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Foto Kegiatan</h3></div>
        <div class="card-body">
            <input type="file" name="foto_kegiatan[]" class="form-control" multiple accept="image/*">
            <small class="form-hint">Bisa pilih beberapa foto sekaligus.</small>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
</form>
@endsection
```

### 17.2 Halaman Detail (arsip)

```blade
{{-- resources/views/notulensi-rapim/show.blade.php --}}
<div class="card mb-3">
    <div class="card-body">
        <h2>{{ $rapim->judul_rapat }}</h2>
        <div class="text-muted mb-3">{{ $rapim->tanggal->format('d F Y') }}</div>
        <h4>Ringkasan Pembahasan</h4>
        <p>{!! $rapim->ringkasan_pembahasan !!}</p>
        <h4>Keputusan / Arahan Pimpinan</h4>
        <p>{!! $rapim->keputusan_arahan !!}</p>
    </div>
</div>

{{-- Lampiran --}}
<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">Lampiran</h3></div>
    <div class="list-group list-group-flush">
        @foreach($rapim->lampiran as $l)
        <a href="{{ Storage::url($l->path) }}" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center">
            <span class="badge bg-secondary-lt me-2">{{ strtoupper($l->tipe_file) }}</span>
            <div>
                <div>{{ $l->nama_file }}</div>
                @if($l->keterangan)<small class="text-muted">{{ $l->keterangan }}</small>@endif
            </div>
        </a>
        @endforeach
    </div>
</div>

{{-- Galeri foto kegiatan --}}
<div class="card">
    <div class="card-header"><h3 class="card-title">Dokumentasi Kegiatan</h3></div>
    <div class="card-body">
        <div class="row row-cards">
            @foreach($rapim->fotoKegiatan as $foto)
            <div class="col-6 col-md-3">
                <a href="{{ Storage::url($foto->path) }}" data-lightbox="galeri-{{ $rapim->id }}" class="d-block">
                    <img src="{{ Storage::url($foto->path) }}" class="img-fluid rounded" alt="{{ $foto->caption }}">
                </a>
                @if($foto->caption)<div class="text-muted small mt-1">{{ $foto->caption }}</div>@endif
            </div>
            @endforeach
        </div>
    </div>
</div>
```

Lightbox foto cukup pakai library ringan (mis. `GLightbox`, via CDN) — konsisten dengan pendekatan "vanilla JS, tanpa jQuery" yang dipakai Tabler.

### 17.3 Daftar/Arsip (list)

Tabel arsip untuk ketiga modul mengikuti pola tabel `fetch` native yang sudah ada (lihat dokumen v5 §5.1): kolom judul/perihal, tanggal, kategori/jenis, jumlah lampiran, jumlah foto, tombol "Lihat". Tambahkan filter tahun dan pencarian teks bebas — karena fase awal ini fungsinya murni sebagai **arsip yang mudah dicari**, bukan dashboard workflow.

---

## 18. Hak Akses (Sederhana)

Untuk fase awal, cukup dua level akses per modul, tidak perlu role granular seperti draft workflow penuh:

| Role | Hak Akses |
|---|---|
| **Admin / Sekretariat** | Input, edit, hapus, unggah lampiran & foto untuk ketiga modul |
| **Pimpinan & staf lain** | Hanya lihat & unduh (read-only) |

Implementasi via satu Policy sederhana per model (`view`, `create`, `update`, `delete`), gate dicek dari `role->kode` seperti pola RBAC yang sudah ada di SiMPEL — tidak perlu policy kontekstual per rapat/komite seperti di desain workflow penuh.

Jika ada dokumen yang sifatnya rahasia (mis. SK terkait kepegawaian tertentu), bisa manfaatkan `AksesKlasifikasiService` yang sudah ada di SiMPEL (dipakai untuk surat masuk/keluar) dengan menambahkan kolom `klasifikasi` opsional di ketiga tabel — tapi ini bisa ditunda sampai benar-benar dibutuhkan.

---

## 19. Struktur Direktori (Fase Awal)

```
simpel-laravel/
├── app/
│   ├── Models/
│   │   ├── SuratKeputusan.php
│   │   ├── KomitePersetujuan.php
│   │   ├── NotulensiRapim.php
│   │   ├── Lampiran.php
│   │   └── FotoKegiatan.php
│   ├── Concerns/
│   │   ├── PunyaLampiran.php
│   │   └── PunyaFotoKegiatan.php
│   └── Policies/
│       ├── SuratKeputusanPolicy.php
│       ├── KomitePersetujuanPolicy.php
│       └── NotulensiRapimPolicy.php
│
├── resources/
│   ├── views/
│   │   ├── surat-keputusan/       (index, form, show)
│   │   ├── komite-persetujuan/    (index, form, show)
│   │   └── notulensi-rapim/       (index, form, show)
│   └── js/simpel/
│       ├── repeater-lampiran.js   # tambah/hapus baris upload lampiran dinamis
│       └── galeri-lightbox.js     # init GLightbox untuk foto kegiatan
```

---

## 20. Jalur ke Fase Berikutnya

Karena tabel fase awal ini (`surat_keputusan`, `komite_persetujuan`, `notulensi_rapim`) dirancang sesederhana mungkin namun tetap **kompatibel arah pengembangan** ke desain workflow penuh di dokumen sebelumnya:

- `komite_persetujuan` di fase awal bisa dipetakan jadi `komite_keputusan` di fase penuh, dengan `proposal` dan `rapat` sebagai tabel tambahan di sekelilingnya.
- `notulensi_rapim` bisa dipetakan jadi kasus khusus dari `notulensi` + `rapat` (jenis_rapat = rapim) di fase penuh.
- `surat_keputusan` di fase awal tinggal ditambah kolom status/workflow dan tabel `sk_riwayat_status`, `sk_penandatangan` bila nanti proses penetapan SK ingin dijalankan **di dalam** sistem, bukan sekadar diarsipkan setelah terbit.
- Tabel `lampiran` dan `foto_kegiatan` yang generik (polymorphic) tetap dipakai apa adanya di fase penuh — tidak perlu migrasi ulang.

Dengan begitu, tim bisa mulai dari kebutuhan paling mendesak (pengarsipan rapi + lampiran + dokumentasi foto) tanpa membangun ulang skema saat nanti ingin menambah alur persetujuan digital penuh.

---

*Dokumen ini adalah versi fase awal (administrasi/pengarsipan) dari tiga modul yang dibahas lebih lengkap sebagai workflow penuh di `system-design-simpel-v5-tambahan-sk-notulensi-komite.md`. Gunakan dokumen ini sebagai acuan implementasi tahap pertama; dokumen sebelumnya menjadi roadmap pengembangan lanjutan.*
