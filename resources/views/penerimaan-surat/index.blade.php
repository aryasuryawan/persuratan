@extends('layouts.app')

@section('content')
<div class="page-header d-print-none mb-5">
    <div class="row g-2 align-items-center">
        <div class="col">
            <div class="page-pretitle">SiMPEL</div>
            <h2 class="page-title">Surat Masuk</h2>
        </div>
        <div class="col-auto ms-auto">
            @can('create', App\Models\SuratMasuk::class)
            <a href="{{ route('penerimaan-surat.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-2"></i>
                Tambah Surat
            </a>
            @endcan
        </div>
    </div>
</div>

<div class="row row-cards row-deck mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card stat-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="subheader">Total Surat</div>
                        <div class="h1 mt-1 mb-0">{{ $total }}</div>
                    </div>
                    <div class="stat-icon bg-primary-subtle">
                        <i class="ti ti-mail fs-2"></i>
                    </div>
                </div>
                <div class="text-muted text-sm mt-2">
                    <span class="text-green d-inline-flex align-items-center lh-1">
                        <i class="ti ti-trending-up me-1"></i>
                        +{{ $hariIni }}
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
                        <div class="h1 mt-1 mb-0">{{ $perStatus->get('received', 0) }}</div>
                    </div>
                    <div class="stat-icon bg-warning-subtle">
                        <i class="ti ti-alert-triangle fs-2"></i>
                    </div>
                </div>
                <div class="text-muted text-sm mt-2">belum diverifikasi</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card stat-info">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="subheader">Dalam Proses</div>
                        <div class="h1 mt-1 mb-0">{{ $perStatus->get('in_progress', 0) + $perStatus->get('assigned', 0) + $perStatus->get('forwarded_to_leadership', 0) }}</div>
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
                        <div class="h1 mt-1 mb-0">{{ $perStatus->get('completed', 0) }}</div>
                    </div>
                    <div class="stat-icon bg-success-subtle">
                        <i class="ti ti-circle-check fs-2"></i>
                    </div>
                </div>
                <div class="text-muted text-sm mt-2">
                    @php
                        $pct = $total > 0 ? round(($perStatus->get('completed', 0) / $total) * 100) : 0;
                    @endphp
                    {{ $pct }}% dari total
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-bordered table-card">
    <div class="card-header">
        <h3 class="card-title">Daftar Surat Masuk</h3>
        <div class="card-actions d-flex gap-2">
            <div class="input-icon">
                <input type="text" id="search-surat" class="form-control form-control-sm" placeholder="Cari nomor agenda, pengirim, perihal...">
                <span class="input-icon-addon">
                    <i class="ti ti-search"></i>
                </span>
            </div>
            <select id="filter-status" class="form-select form-select-sm" style="width: 180px;">
                <option value="">Semua Status</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->kode }}">{{ $s->label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-vcenter card-table" id="tabel-surat" data-endpoint="{{ url('/api/surat-masuk') }}">
            <thead>
                <tr>
                    <th>No. Agenda / Pengirim</th>
                    <th>Perihal</th>
                    <th>Tgl Diterima</th>
                    <th>Status</th>
                    <th>Klasifikasi</th>
                    <th class="text-end" style="width: 1%">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabel-surat-body">
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <div id="tabel-surat-info" class="text-muted"></div>
        <ul class="pagination ms-auto mb-0" id="tabel-surat-pagination"></ul>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabel = document.getElementById('tabel-surat');
    if (tabel) {
        new SuratTable(tabel.dataset.endpoint || '/api/surat-masuk');
    }
});
</script>
@endpush
@endsection
