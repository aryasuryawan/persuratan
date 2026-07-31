@extends('layouts.app')

@section('content')
<div class="page-header d-print-none mb-5">
    <div class="row g-2 align-items-center">
        <div class="col">
            <div class="page-pretitle">SiMPEL</div>
            <h2 class="page-title">Dashboard</h2>
        </div>
    </div>
</div>

<div class="row row-cards row-deck mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card stat-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="subheader">Surat Masuk</div>
                        <div class="h1 mt-1 mb-0">{{ $totalSuratMasuk ?? 0 }}</div>
                    </div>
                    <div class="stat-icon bg-primary-subtle">
                        <i class="ti ti-mail fs-2"></i>
                    </div>
                </div>
                <div class="text-muted text-sm mt-2">
                    <span class="text-green d-inline-flex align-items-center lh-1">
                        <i class="ti ti-trending-up me-1"></i>
                        +{{ $suratHariIni ?? 0 }}
                    </span>
                    hari ini
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card stat-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="subheader">Perlu Verifikasi</div>
                        <div class="h1 mt-1 mb-0">{{ $perluVerifikasi ?? 0 }}</div>
                    </div>
                    <div class="stat-icon bg-warning-subtle">
                        <i class="ti ti-alert-triangle fs-2"></i>
                    </div>
                </div>
                <div class="text-muted text-sm mt-2">
                    <a href="{{ route('verifikasi-surat.index') }}" class="text-decoration-none">
                        Lihat semua
                        <i class="ti ti-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card stat-info">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="subheader">Dalam Proses</div>
                        <div class="h1 mt-1 mb-0">{{ $dalamProses ?? 0 }}</div>
                    </div>
                    <div class="stat-icon bg-info-subtle">
                        <i class="ti ti-clock-play fs-2"></i>
                    </div>
                </div>
                <div class="text-muted text-sm mt-2">sedang diproses</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card stat-success">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="subheader">Selesai</div>
                        <div class="h1 mt-1 mb-0">{{ $selesai ?? 0 }}</div>
                    </div>
                    <div class="stat-icon bg-success-subtle">
                        <i class="ti ti-circle-check fs-2"></i>
                    </div>
                </div>
                <div class="text-muted text-sm mt-2">
                    {{ $persenSelesai ?? 0 }}% dari total
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row row-deck">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header">
                <h3 class="card-title">Surat Masuk Terbaru</h3>
                <div class="card-actions">
                    <a href="{{ route('penerimaan-surat.index') }}" class="btn btn-sm btn-ghost-secondary">
                        Lihat Semua
                        <i class="ti ti-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No. Agenda</th>
                            <th>Pengirim</th>
                            <th>Perihal</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suratTerbaru ?? [] as $surat)
                        <tr>
                            <td><span class="fw-semibold">{{ $surat->nomor_agenda }}</span></td>
                            <td>{{ $surat->pengirim }}</td>
                            <td class="text-truncate" style="max-width: 250px;">{{ $surat->perihal }}</td>
                            <td class="text-muted">{{ $surat->tanggal_diterima->format('d/m/Y') }}</td>
                            <td><x-badge-status :status="$surat->status->kode" /></td>
                            <td>
                                <a href="{{ route('penerimaan-surat.show', $surat->uuid) }}" class="btn btn-ghost-secondary btn-sm btn-icon">
                                    <i class="ti ti-chevron-right"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada surat masuk</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card action-card">
            <div class="card-header">
                <h3 class="card-title">Aksi Cepat</h3>
            </div>
            <div class="card-body">
                <div class="btn-list vertical btn-stacked">
                    @can('create', App\Models\SuratMasuk::class)
                    <a href="{{ route('penerimaan-surat.create') }}" class="btn btn-outline-primary">
                        <i class="ti ti-mail-forward me-2"></i>
                        Surat Masuk Baru
                    </a>
                    @endcan

                    @can('create', App\Models\SuratKeluar::class)
                    <a href="{{ route('surat-keluar.create') }}" class="btn btn-outline-blue">
                        <i class="ti ti-mail-opened me-2"></i>
                        Surat Keluar Baru
                    </a>
                    @endcan

                    <a href="{{ route('tracking.index') }}" class="btn btn-outline-teal">
                        <i class="ti ti-search me-2"></i>
                        Tracking Surat
                    </a>

                    <a href="{{ route('disposisi.index') }}" class="btn btn-outline-orange">
                        <i class="ti ti-send me-2"></i>
                        Disposisi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection