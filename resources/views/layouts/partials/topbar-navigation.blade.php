@php $role = auth()->user()->role->kode; @endphp

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <span class="ti ti-home"></span>
        </span>
        <span class="nav-link-title">Dashboard</span>
    </a>
</li>

@if(in_array($role, ['cc_security', 'sekretaris', 'unit_lain', 'admin']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('penerimaan-surat*') ? 'active' : '' }}" href="{{ route('penerimaan-surat.index') }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <span class="ti ti-mail"></span>
        </span>
        <span class="nav-link-title">Surat Masuk</span>
    </a>
</li>
@endif

@if(in_array($role, ['sekretaris', 'admin']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('verifikasi-surat*') ? 'active' : '' }}" href="{{ route('verifikasi-surat.index') }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <span class="ti ti-circle-check"></span>
        </span>
        <span class="nav-link-title">
            Verifikasi
            @php $pending = \App\Models\SuratMasuk::where('status_id', statusId('received'))->count(); @endphp
            @if($pending > 0)
                <span class="badge bg-red text-red-fg ms-2">{{ $pending }}</span>
            @endif
        </span>
    </a>
</li>
@endif

@if(in_array($role, ['sekretaris', 'pimpinan', 'admin']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('disposisi*') ? 'active' : '' }}" href="{{ route('disposisi.index') }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <span class="ti ti-message-2"></span>
        </span>
        <span class="nav-link-title">Disposisi</span>
    </a>
</li>
@endif

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <span class="ti ti-archive"></span>
        </span>
        <span class="nav-link-title">Arsip Lainnya</span>
    </a>
    <div class="dropdown-menu">
        @if(in_array($role, ['sekretaris', 'unit_lain', 'admin']))
        <a class="dropdown-item {{ request()->routeIs('surat-keluar*') ? 'active' : '' }}" href="{{ route('surat-keluar.index') }}">Surat Keluar</a>
        @endif
        <a class="dropdown-item {{ request()->routeIs('surat-keputusan*') ? 'active' : '' }}" href="{{ route('surat-keputusan.index') }}">Surat Keputusan</a>
        <a class="dropdown-item {{ request()->routeIs('komite-persetujuan*') ? 'active' : '' }}" href="{{ route('komite-persetujuan.index') }}">Hasil Komite Persetujuan</a>
        <a class="dropdown-item {{ request()->routeIs('notulensi-rapim*') ? 'active' : '' }}" href="{{ route('notulensi-rapim.index') }}">Notulensi Rapim</a>
    </div>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('tracking*') ? 'active' : '' }}" href="{{ route('tracking.index') }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <span class="ti ti-route"></span>
        </span>
        <span class="nav-link-title">Tracking</span>
    </a>
</li>

@if($role === 'admin')
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <span class="ti ti-database"></span>
        </span>
        <span class="nav-link-title">Master Data</span>
    </a>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ route('master.direktorats.index') }}">Direktorat</a>
            <a class="dropdown-item" href="{{ route('master.strukturs.index') }}">Struktur</a>
            <a class="dropdown-item" href="{{ route('master.organisasi-pengirims.index') }}">Organisasi Pengirim</a>
            <a class="dropdown-item" href="{{ route('master.divisis.index') }}">Divisi</a>
            <a class="dropdown-item" href="{{ route('master.departemens.index') }}">Departemen</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('master.roles.index') }}">Role</a>
            <a class="dropdown-item" href="{{ route('master.kode-surats.index') }}">Kode Surat</a>
            <a class="dropdown-item" href="{{ route('master.klasifikasi.index') }}">Klasifikasi</a>
            <a class="dropdown-item" href="{{ route('master.users.index') }}">User</a>
            <a class="dropdown-item" href="{{ route('master.sekretaris-pimpinan.index') }}">Sekretaris–Pimpinan</a>
            <a class="dropdown-item" href="{{ route('master.settings.index') }}">Pengaturan</a>
        </div>
</li>
@endif
