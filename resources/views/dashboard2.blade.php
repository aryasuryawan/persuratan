@extends('layouts.app')

@section('title', 'Dashboard 2 — SiMPEL')

@section('content')
<div class="page-header d-print-none mb-5">
    <div class="row g-2 align-items-center">
        <div class="col">
            <div class="page-pretitle">SiMPEL</div>
            <h2 class="page-title">Dashboard 2</h2>
        </div>
        <div class="col-auto ms-auto">
            <a href="{{ route('dashboard') }}" class="btn btn-ghost-secondary">
                <i class="ti ti-layout-dashboard me-2"></i>Dashboard Lama
            </a>
        </div>
    </div>
</div>

{{-- Row 1: Stat Cards --}}
<div class="row row-cards mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Surat Masuk</div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-0 me-2">{{ number_format($totalSuratMasuk) }}</div>
                    <div class="me-auto">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            <i class="ti ti-trending-up"></i>
                            +{{ $suratHariIni }}
                        </span>
                    </div>
                </div>
                <div class="text-secondary mt-2">{{ $suratHariIni }} surat hari ini</div>
            </div>
            <div id="chart-visitors" class="position-relative"></div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Perlu Verifikasi</div>
                <div class="d-flex align-items-baseline mb-2">
                    <div class="h1 mb-0 me-2">{{ number_format($perluVerifikasi) }}</div>
                    <div class="me-auto">
                        <span class="text-red d-inline-flex align-items-center lh-1">
                            <i class="ti ti-trending-down"></i>
                            {{ $perluVerifikasi > 0 ? $perluVerifikasi : 0 }}
                        </span>
                    </div>
                </div>
                <div id="chart-active-users-3" class="position-relative"></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Dalam Proses</div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-0 me-2">{{ number_format($dalamProses) }}</div>
                    <div class="me-auto">
                        <span class="text-yellow d-inline-flex align-items-center lh-1">
                            <i class="ti ti-minus"></i>
                            0%
                        </span>
                    </div>
                </div>
                <div class="text-secondary mt-2">{{ $dalamProses }} sedang diproses</div>
            </div>
            <div id="chart-revenue-bg" class="position-relative rounded-bottom chart-sm"></div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Selesai</div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-3 me-2">{{ number_format($selesai) }}</div>
                    <div class="me-auto">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            <i class="ti ti-trending-up"></i>
                            {{ $persenSelesai }}%
                        </span>
                    </div>
                </div>
                <div id="chart-new-clients" class="position-relative chart-sm"></div>
            </div>
        </div>
    </div>
</div>

{{-- Row 2: Traffic + Status Donut --}}
<div class="row row-cards mb-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Surat Masuk (30 Hari)</h3>
                <div id="chart-mentions" class="position-relative" style="height: 280px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Status Surat</h3>
                <div id="chart-status-donut" class="position-relative" style="height: 280px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Row 3: Activity + Most Visited Pages --}}
<div class="row row-cards mb-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0">
                <div class="card-title">Aktivitas Surat</div>
            </div>
            <div class="position-relative">
                <div class="position-absolute top-0 left-0 px-3 mt-1 w-75">
                    <div class="row g-2">
                        <div class="col-auto">
                            <div class="chart-sparkline chart-sparkline-square" id="sparkline-activity"></div>
                        </div>
                        <div class="col">
                            <div>Hari ini: {{ $suratHariIni }} surat</div>
                            <div class="text-secondary">
                                @if($suratKemarin > 0)
                                    @php $diff = round((($suratHariIni - $suratKemarin) / $suratKemarin) * 100); @endphp
                                    <i class="ti ti-trending-up icon-inline text-green"></i>
                                    {{ $diff > 0 ? '+' : '' }}{{ $diff }}% dari kemarin
                                @else
                                    <i class="ti ti-minus icon-inline text-yellow"></i>
                                    Kemarin 0 surat
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div id="chart-development-activity" class="position-relative" style="height: 192px;"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Surat Terbaru</h3>
            </div>
            <div class="card-table table-responsive">
                <table class="table table-vcenter">
                    <thead>
                        <tr>
                            <th>No. Agenda</th>
                            <th>Pengirim</th>
                            <th>Perihal</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suratTerbaru as $surat)
                        <tr>
                            <td class="fw-semibold">{{ $surat->nomor_agenda }}</td>
                            <td class="text-secondary">{{ $surat->pengirim }}</td>
                            <td class="td-truncate">
                                <div class="text-truncate" style="max-width: 200px;">{{ $surat->perihal }}</div>
                            </td>
                            <td class="text-nowrap text-secondary">{{ $surat->tanggal_diterima->format('d M Y') }}</td>
                            <td><x-badge-status :status="$surat->status->kode" /></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada surat</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Row 4: Surat Keluar + Quick Actions --}}
<div class="row row-cards mb-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Surat Keluar</div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-0 me-2">{{ number_format($suratKeluar) }}</div>
                </div>
                <div id="chart-active-users" class="position-relative chart-sm mt-3"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card action-card">
            <div class="card-header">
                <h3 class="card-title">Aksi Cepat</h3>
            </div>
            <div class="card-body">
                <div class="btn-list vertical btn-stacked">
                    @can('create', App\Models\SuratMasuk::class)
                    <a href="{{ route('penerimaan-surat.create') }}" class="btn btn-outline-teal">
                        <i class="ti ti-mail-forward me-2"></i>
                        Surat Masuk Baru
                    </a>
                    @endcan

                    @can('create', App\Models\SuratKeluar::class)
                    <a href="{{ route('surat-keluar.create') }}" class="btn btn-outline-primary">
                        <i class="ti ti-mail-opened me-2"></i>
                        Surat Keluar Baru
                    </a>
                    @endcan

                    <a href="{{ route('tracking.index') }}" class="btn btn-outline-blue">
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

@push('scripts')
<script>
    window.Dashboard2Data = {
        dailyLabels: @json($dailyLabels),
        dailyCounts: @json($dailyCounts),
        statusData: @json($statusData),
        suratHariIni: {{ $suratHariIni }},
        suratKemarin: {{ $suratKemarin }},
    };
</script>
@endpush
@endsection
