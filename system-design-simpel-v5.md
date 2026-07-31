# System Design — SiMPEL v5

Aplikasi manajemen surat masuk & keluar untuk Yayasan Dompet Dhuafa Republika — rebuild dari CodeIgniter 3 ke **Laravel 13**, dengan model **Sekretaris-sebagai-Proxy** (v4) dan penyesuaian frontend ke **Tabler** (v5).

> **Perubahan utama v4 → v5:** Seluruh referensi AdminLTE 4 diganti dengan **Tabler** (Bootstrap 5.3, vanilla JS, 40k+ GitHub stars, MIT license). Tabulator digantikan oleh **DataTable vanilla JS bawaan Tabler** untuk tabel server-side. Semua kelas CSS, komponen, dan konvensi layout mengikuti sistem Tabler.

---

## 1. Mengapa Tabler (bukan AdminLTE 4)

| Aspek | AdminLTE 4 | Tabler |
|---|---|---|
| GitHub Stars | ~44k | ~40k |
| Bootstrap versi | 5.3 | 5.3 |
| jQuery | ❌ (vanilla JS) | ❌ (vanilla JS) |
| Dark mode bawaan | ❌ | ✅ |
| Komponen UI | ~100 | 100+ (lebih polished) |
| Sample pages | ~40 | 200+ |
| Icon set | Font Awesome | Tabler Icons (6.000+ SVG, MIT) |
| Tabel | Tabulator (perlu setup) | DataTable bawaan + integrasi mudah |
| Desain | Classic dashboard | Modern, clean, lebih "product-like" |
| Update terakhir | Aktif | Aktif (Bootstrap 5.3.7, 2026) |
| Lisensi | MIT | MIT |

Untuk SiMPEL, Tabler lebih cocok karena desainnya lebih bersih untuk internal tool sehari-hari, icon set bawaan sudah sangat lengkap (tidak perlu Font Awesome terpisah), dan komponen seperti timeline, badge status, dan card sudah siap pakai tanpa kustomisasi berat.

---

## 2. Perubahan Tech Stack

| Komponen | v4 (sebelumnya) | v5 (sekarang) |
|---|---|---|
| Framework | Laravel 13 | Laravel 13 ✅ sama |
| Frontend template | **AdminLTE 4** (Bootstrap 5.3, vanilla JS) | **Tabler** (Bootstrap 5.3, vanilla JS) |
| Icon | Font Awesome | **Tabler Icons** (SVG inline atau via CDN) |
| Tabel | **Tabulator** (library terpisah) | **DataTable Tabler** (bawaan) atau Tom Select untuk dropdown |
| Chart | Chart.js | **Apache ECharts** (default Tabler) atau tetap Chart.js |
| CSS build | npm + Vite (AdminLTE asset) | **npm + Vite** (Tabler CSS/JS via npm package) |
| Komponen JS | AdminLTE JS | **Tabler JS** (`@tabler/core`) |
| Rich text | — | **HugeRTE** (bawaan Tabler, pengganti TinyMCE) |
| Notifikasi inline | Flash message Bootstrap | **Tabler Alert / Toast** |

Semua komponen lain (Laravel 13, MySQL 8.0, Eloquent, Laravel Breeze, Policy, Event/Listener, Scheduler) **tidak berubah dari v4**.

---

## 3. Instalasi & Setup Tabler di Laravel

### 3.1 Install via npm

```bash
npm install @tabler/core @tabler/icons-webfont
```

### 3.2 `resources/css/app.css`

```css
/* Import Tabler core CSS */
@import "@tabler/core/dist/css/tabler.min.css";
@import "@tabler/core/dist/css/tabler-flags.min.css"; /* opsional */

/* Override custom untuk SiMPEL */
:root {
    --tblr-primary: #206bc4;   /* warna brand DD bisa disesuaikan */
    --tblr-font-sans-serif: 'Inter', sans-serif;
}

/* Custom badge status surat */
.badge-received         { background-color: var(--tblr-secondary); }
.badge-verified         { background-color: var(--tblr-info); }
.badge-forwarded        { background-color: var(--tblr-warning); color: #000; }
.badge-assigned         { background-color: var(--tblr-primary); }
.badge-in-progress      { background-color: var(--tblr-azure); }
.badge-completed        { background-color: var(--tblr-success); }
.badge-archived         { background-color: var(--tblr-muted); }
```

### 3.3 `resources/js/app.js`

```js
// Import Tabler JS
import { TablerApp } from '@tabler/core';

// Import komponen custom SiMPEL
import './simpel/tracking-chart.js';
import './simpel/disposisi-form.js';
import './simpel/surat-table.js';

// Init Tabler
document.addEventListener('DOMContentLoaded', () => {
    TablerApp.init();
});
```

### 3.4 `vite.config.js`

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

---

## 4. Layout & Struktur Blade

### 4.1 Layout Utama (`resources/views/layouts/app.blade.php`)

Struktur HTML Tabler berbeda dari AdminLTE. Tabler menggunakan pola `page-wrapper` + `navbar` + `navbar-vertical` (sidebar):

```html
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <title>SiMPEL — @yield('title', 'Manajemen Surat')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div class="wrapper">

    {{-- Sidebar --}}
    <aside class="navbar navbar-vertical navbar-expand-lg">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark">
                <a href="{{ route('dashboard') }}">SiMPEL</a>
            </h1>
            <div class="collapse navbar-collapse" id="navbar-menu">
                <ul class="navbar-nav pt-lg-3">
                    @include('layouts.partials.sidebar-menu')
                </ul>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="page-wrapper">
        {{-- Top navbar --}}
        <div class="navbar-expand-md">
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="navbar navbar-light">
                    <div class="container-xl">
                        <ul class="navbar-nav flex-row order-md-last">
                            @include('layouts.partials.topbar-user')
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page header --}}
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title">@yield('page-title')</h2>
                        @hasSection('breadcrumb')
                            <ol class="breadcrumb">@yield('breadcrumb')</ol>
                        @endif
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        @yield('page-actions')
                    </div>
                </div>
            </div>
        </div>

        {{-- Page body --}}
        <div class="page-body">
            <div class="container-xl">
                @include('layouts.partials.flash-messages')
                @yield('content')
            </div>
        </div>

        @include('layouts.partials.footer')
    </div>
</div>
</body>
</html>
```

### 4.2 Flash Message (Tabler Alert)

```blade
{{-- resources/views/layouts/partials/flash-messages.blade.php --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
    <div class="d-flex">
        <div><svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg></div>
        <div>{{ session('success') }}</div>
    </div>
    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible" role="alert">
    <div>{{ session('error') }}</div>
    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
</div>
@endif
```

### 4.3 Sidebar Menu Dinamis per Role

```blade
{{-- resources/views/layouts/partials/sidebar-menu.blade.php --}}

@php $role = auth()->user()->role->kode; @endphp

{{-- Semua role: Dashboard --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" ...>...</svg>
        </span>
        <span class="nav-link-title">Dashboard</span>
    </a>
</li>

@if(in_array($role, ['cc_security', 'sekretaris', 'unit_lain', 'admin']))
<li class="nav-item">
    <a class="nav-link" href="{{ route('penerimaan-surat.index') }}">
        <span class="nav-link-icon">...</span>
        <span class="nav-link-title">Surat Masuk</span>
    </a>
</li>
@endif

@if(in_array($role, ['sekretaris', 'admin']))
<li class="nav-item">
    <a class="nav-link" href="{{ route('verifikasi-surat.index') }}">
        <span class="nav-link-icon">...</span>
        <span class="nav-link-title">Verifikasi</span>
        @php $pending = \App\Models\SuratMasuk::where('status_id', statusId('received'))->count(); @endphp
        @if($pending > 0)
            <span class="badge bg-red ms-auto">{{ $pending }}</span>
        @endif
    </a>
</li>
@endif

@if(in_array($role, ['sekretaris', 'pimpinan', 'admin']))
<li class="nav-item">
    <a class="nav-link" href="{{ route('disposisi.index') }}">
        <span class="nav-link-title">Disposisi</span>
    </a>
</li>
@endif

@if(in_array($role, ['sekretaris', 'unit_lain', 'admin']))
<li class="nav-item">
    <a class="nav-link" href="{{ route('surat-keluar.index') }}">
        <span class="nav-link-title">Surat Keluar</span>
    </a>
</li>
@endif

<li class="nav-item">
    <a class="nav-link" href="{{ route('tracking.index') }}">
        <span class="nav-link-title">Tracking</span>
    </a>
</li>

@if($role === 'admin')
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
        <span class="nav-link-title">Master Data</span>
    </a>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('master.roles.index') }}">Role</a>
        <a class="dropdown-item" href="{{ route('master.klasifikasi.index') }}">Klasifikasi</a>
        <a class="dropdown-item" href="{{ route('master.users.index') }}">User</a>
        <a class="dropdown-item" href="{{ route('master.sekretaris-pimpinan.index') }}">Sekretaris–Pimpinan</a>
    </div>
</li>
@endif
```

---

## 5. Komponen UI Tabler untuk Setiap Modul

### 5.1 Tabel Surat (mengganti Tabulator)

Tabler menyediakan tabel responsif bawaan yang bisa dikombinasi dengan AJAX via `fetch` native. Untuk server-side processing, gunakan `fetch` ke endpoint JSON Laravel:

```html
{{-- resources/views/penerimaan-surat/index.blade.php --}}

@extends('layouts.app')
@section('page-title', 'Surat Masuk')
@section('page-actions')
    @can('create', \App\Models\SuratMasuk::class)
    <a href="{{ route('penerimaan-surat.create') }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" ...>...</svg>
        Tambah Surat
    </a>
    @endcan
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-options">
            <input type="text" id="search-surat" class="form-control form-control-sm" placeholder="Cari...">
            <select id="filter-status" class="form-select form-select-sm ms-2">
                <option value="">Semua Status</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->kode }}">{{ $s->label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table" id="tabel-surat">
            <thead>
                <tr>
                    <th>No. Agenda</th>
                    <th>Pengirim</th>
                    <th>Perihal</th>
                    <th>Tgl Diterima</th>
                    <th>Status</th>
                    <th>Klasifikasi</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody id="tabel-surat-body">
                {{-- Diisi via JS fetch --}}
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <div id="tabel-surat-info" class="text-muted"></div>
        <ul class="pagination ms-auto mb-0" id="tabel-surat-pagination"></ul>
    </div>
</div>
@endsection
```

```js
// resources/js/simpel/surat-table.js

class SuratTable {
    constructor(endpoint) {
        this.endpoint = endpoint;
        this.page = 1;
        this.search = '';
        this.status = '';
        this.init();
    }

    init() {
        document.getElementById('search-surat')
            ?.addEventListener('input', (e) => {
                this.search = e.target.value;
                this.page = 1;
                this.fetch();
            });
        document.getElementById('filter-status')
            ?.addEventListener('change', (e) => {
                this.status = e.target.value;
                this.page = 1;
                this.fetch();
            });
        this.fetch();
    }

    async fetch() {
        const url = new URL(this.endpoint, window.location.origin);
        url.searchParams.set('page', this.page);
        url.searchParams.set('search', this.search);
        url.searchParams.set('status', this.status);

        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        this.render(data);
    }

    render(data) {
        const tbody = document.getElementById('tabel-surat-body');
        tbody.innerHTML = data.data.map(s => `
            <tr>
                <td>${s.nomor_agenda}</td>
                <td>${s.pengirim}</td>
                <td>${s.perihal}</td>
                <td>${s.tanggal_diterima}</td>
                <td><span class="badge badge-${s.status_kode}">${s.status_label}</span></td>
                <td><span class="badge bg-${s.klasifikasi_color}">${s.klasifikasi}</span></td>
                <td>
                    <a href="/penerimaan-surat/${s.uuid}" class="btn btn-sm">Detail</a>
                </td>
            </tr>
        `).join('');
        // render pagination...
    }
}

const tabel = document.getElementById('tabel-surat');
if (tabel) {
    new SuratTable('/api/surat-masuk');
}
```

### 5.2 Badge Status Surat

```blade
{{-- resources/views/components/badge-status.blade.php --}}
@props(['status'])

@php
$map = [
    'received'               => ['color' => 'secondary', 'label' => 'Diterima'],
    'verified'               => ['color' => 'info',      'label' => 'Diverifikasi'],
    'forwarded_to_leadership'=> ['color' => 'warning',   'label' => 'Ke Pimpinan'],
    'assigned'               => ['color' => 'primary',   'label' => 'Didisposisikan'],
    'handled_by_leadership'  => ['color' => 'purple',    'label' => 'Pimpinan TL'],
    'in_progress'            => ['color' => 'azure',     'label' => 'Diproses'],
    'completed'              => ['color' => 'success',   'label' => 'Selesai'],
    'archived'               => ['color' => 'dark',      'label' => 'Diarsipkan'],
];
$s = $map[$status] ?? ['color' => 'secondary', 'label' => $status];
@endphp

<span class="badge bg-{{ $s['color'] }}">{{ $s['label'] }}</span>
```

### 5.3 Timeline Riwayat Status (Tabler Steps/Timeline)

Tabler punya komponen `timeline` yang cocok untuk riwayat status surat:

```html
{{-- resources/views/tracking/timeline.blade.php --}}

<ul class="timeline">
    @foreach($riwayat as $r)
    <li class="timeline-event">
        <div class="timeline-event-icon bg-{{ $r->warna }}">
            <svg ...>...</svg>
        </div>
        <div class="card timeline-event-card">
            <div class="card-body">
                <div class="text-muted float-end">{{ $r->waktu->diffForHumans() }}</div>
                <h4>{{ $r->status_ke_label }}</h4>
                <p class="text-muted mb-1">
                    Oleh: <strong>{{ $r->dilakukanOleh->name }}</strong>
                    @if($r->atasNamaPimpinan && $r->atasNamaPimpinan->id !== $r->dilakukan_oleh_id)
                        <span class="text-muted"> — atas nama </span>
                        <strong>{{ $r->atasNamaPimpinan->name }}</strong>
                    @endif
                </p>
                @if($r->catatan)
                    <p class="mb-0">{{ $r->catatan }}</p>
                @endif
            </div>
        </div>
    </li>
    @endforeach
</ul>
```

### 5.4 Dashboard Tracking (Card Stats + ECharts)

```html
{{-- resources/views/tracking/dashboard.blade.php --}}

{{-- Card stats per status --}}
<div class="row row-deck row-cards">
    @foreach($stats as $stat)
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">{{ $stat['label'] }}</div>
                </div>
                <div class="h1 mb-3">{{ $stat['count'] }}</div>
                <div class="d-flex mb-2">
                    <div>surat {{ strtolower($stat['label']) }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Chart ECharts --}}
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Tren Surat Masuk (30 hari terakhir)</h3>
    </div>
    <div class="card-body">
        <div id="chart-tren" style="height: 300px;" data-url="{{ route('api.tracking.chart') }}"></div>
    </div>
</div>
```

### 5.5 Form Disposisi (Modal atau Full Page)

Untuk UX yang lebih baik, form disposisi bisa ditampilkan sebagai **Tabler Modal** (tanpa reload) saat dipanggil dari daftar surat, atau sebagai full page untuk detail:

```html
{{-- Modal disposisi --}}
<div class="modal modal-blur fade" id="modal-disposisi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Disposisi Surat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="form-disposisi">
                @csrf
                <div class="modal-body">
                    {{-- Dropdown Atas Nama (hanya untuk Sekretaris) --}}
                    @if(auth()->user()->role->kode === 'sekretaris')
                    <div class="mb-3">
                        <label class="form-label required">Atas Nama Pimpinan</label>
                        <select name="atas_nama_pimpinan_id" class="form-select" required>
                            <option value="">-- Pilih Pimpinan --</option>
                            @foreach(auth()->user()->pimpinanYangDilayani as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-hint">Disposisi dicatat atas nama pimpinan yang dipilih.</small>
                    </div>
                    @endif

                    {{-- Jenis Disposisi --}}
                    <div class="mb-3">
                        <label class="form-label required">Jenis Disposisi</label>
                        <div class="form-selectgroup">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="jenis" value="ke_pelaksana" class="form-selectgroup-input" id="jenis-pelaksana">
                                <span class="form-selectgroup-label">Ke Pelaksana</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="jenis" value="langsung_pimpinan" class="form-selectgroup-input" id="jenis-langsung">
                                <span class="form-selectgroup-label">Tindak Lanjut Langsung</span>
                            </label>
                        </div>
                    </div>

                    {{-- Conditional: ke pelaksana --}}
                    <div id="section-pelaksana" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">Pelaksana</label>
                            <select name="pelaksana_id" class="form-select">
                                {{-- diisi via fetch atau server render --}}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deadline</label>
                            <input type="date" name="tanggal_deadline" class="form-control">
                        </div>
                    </div>

                    {{-- Conditional: langsung pimpinan --}}
                    <div id="section-langsung" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">Catatan Tindak Lanjut</label>
                            <textarea name="catatan_pimpinan" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Disposisi</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

## 6. Struktur Direktori (Diperbarui)

```
simpel-laravel/
├── app/                          (sama seperti v4)
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php             # Layout utama Tabler (page-wrapper)
│   │   │   └── partials/
│   │   │       ├── sidebar-menu.blade.php
│   │   │       ├── topbar-user.blade.php
│   │   │       ├── flash-messages.blade.php
│   │   │       └── footer.blade.php
│   │   ├── components/
│   │   │   ├── badge-status.blade.php    # <x-badge-status :status="$surat->status->kode" />
│   │   │   ├── badge-klasifikasi.blade.php
│   │   │   ├── modal-disposisi.blade.php
│   │   │   └── timeline-item.blade.php
│   │   ├── penerimaan-surat/
│   │   ├── verifikasi-surat/
│   │   ├── disposisi/
│   │   ├── tracking/
│   │   ├── surat-keluar/
│   │   └── master/
│   │
│   ├── js/
│   │   ├── app.js                        # Init Tabler + import modul
│   │   └── simpel/
│   │       ├── surat-table.js            # Tabel fetch native (ganti Tabulator)
│   │       ├── tracking-chart.js         # ECharts setup + polling
│   │       ├── disposisi-form.js         # Toggle conditional section disposisi
│   │       └── nomor-surat.js            # Saran nomor surat keluar
│   │
│   └── css/
│       └── app.css                       # Import Tabler + override SiMPEL
│
├── package.json
│   # dependencies: @tabler/core, @tabler/icons-webfont
│   # devDependencies: vite, laravel-vite-plugin
│
└── vite.config.js
```

---

## 7. Hal yang Tidak Berubah dari v4

Semua arsitektur backend, business logic, dan database tetap sama:

- Model Sekretaris-sebagai-Proxy (tabel `sekretaris_pimpinan`, kolom proxy di `disposisi` & `riwayat_status`)
- `SuratScopeService`, `WorkflowService`, `AksesKlasifikasiService`
- Semua Actions (`BuatDisposisi`, `VerifikasiSurat`, `ArsipakanSurat`)
- Laravel Policy & Gate (RBAC + klasifikasi)
- Audit trail via Event/Listener (`SuratStatusChanged` → `CatatRiwayatStatus`)
- SLA Reminder via Laravel Scheduler
- Enkripsi lampiran Rahasia (`Storage::disk('rahasia')` + `Crypt`)
- Notifikasi sinkron (Laravel Notification, channel mail + database)
- Strategi migrasi dari CI3 (Strangler pattern, 4 fase)
- Autentikasi via Laravel Breeze

---

*Dokumen ini adalah revisi dari v4: mengganti frontend template dari AdminLTE 4 ke Tabler, menyesuaikan konvensi CSS/JS/HTML ke sistem Tabler (page-wrapper, navbar-vertical, card, badge, timeline, modal), mengganti Tabulator dengan fetch native + tabel Tabler, dan mengganti ikon Font Awesome dengan Tabler Icons. Semua backend, database, dan business logic tidak berubah dari v4.*
