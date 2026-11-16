@extends('layouts.app')

@section('content')
<div class="page-header d-print-none mb-5">
    <div class="row g-2 align-items-center">
        <div class="col">
            <div class="page-pretitle">SiMPEL</div>
            <h2 class="page-title">Surat Masuk</h2>
        </div>
        <div class="col-auto ms-auto d-flex gap-2">
            <a href="{{ route('penerimaan-surat.export') }}" id="btn-export-surat" class="btn btn-outline-success">
                <i class="ti ti-file-spreadsheet me-2"></i>
                Export Excel
            </a>
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
                        <div class="h1 mt-1 mb-0">{{ $perStatus->get('in_progress', 0) + $perStatus->get('assigned', 0) + $perStatus->get('forwarded_to_leadership', 0) + $perStatus->get('verified', 0) + $perStatus->get('handled_by_leadership', 0) }}</div>
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
    </div>
    <div class="card-body border-bottom py-3">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-4 col-lg-3">
                <div class="input-icon">
                    <input type="text" id="search-surat" class="form-control form-control-sm" placeholder="Cari nomor agenda, pengirim, perihal...">
                    <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->kode }}">{{ $s->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <select id="filter-klasifikasi" class="form-select form-select-sm">
                    <option value="">Semua Klasifikasi</option>
                    @foreach($klasifikasi as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <select id="filter-topik" class="form-select form-select-sm">
                    <option value="">Semua Topik</option>
                    @foreach($topiks as $t)
                        <option value="{{ $t->id }}">{{ $t->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-auto">
                <x-filter-periode />
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <select id="filter-inputer" class="form-select form-select-sm">
                    <option value="">Semua Inputer</option>
                    @foreach($inputers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-vcenter card-table" id="tabel-surat" data-endpoint="{{ url('/api/surat-masuk') }}">
            <thead>
                <tr>
                    <th>No. Agenda / Pengirim</th>
                    <th>Organisasi Pengirim</th>
                    <th>Perihal</th>
                    <th>Tgl Diterima</th>
                    <th>Status</th>
                    <th>Klasifikasi</th>
                    <th>Topik</th>
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

    const btnExport = document.getElementById('btn-export-surat');
    btnExport?.addEventListener('click', (e) => {
        e.preventDefault();
        const base = btnExport.getAttribute('href');
        const params = new URLSearchParams();
        const map = {
            search: 'search-surat',
            status: 'filter-status',
            klasifikasi_id: 'filter-klasifikasi',
            topik_id: 'filter-topik',
            periode_dari: 'filter-periode-dari',
            periode_sampai: 'filter-periode-sampai',
            inputer_id: 'filter-inputer',
        };
        Object.entries(map).forEach(([key, id]) => {
            const v = document.getElementById(id)?.value;
            if (v) params.set(key, v);
        });
        window.location.href = base + (params.toString() ? ('?' + params.toString()) : '');
    });
});
</script>
@endpush
@endsection
