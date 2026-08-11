@extends('layouts.app')

@section('page-title', 'Master Direktorat')

@section('page-header')
@endsection

@section('page-actions')
<div class="d-flex gap-2 align-items-center">
    <div class="input-icon">
        <input type="text" id="search-direktorat" class="form-control form-control-sm" placeholder="Cari kode, nama..." style="width: 220px;">
        <span class="input-icon-addon">
            <i class="ti ti-search"></i>
        </span>
    </div>
    <select id="filter-aktif" class="form-select form-select-sm" style="width: 120px;">
        <option value="">Semua</option>
        <option value="1">Aktif</option>
        <option value="0">Nonaktif</option>
    </select>
    <a href="{{ route('master.direktorats.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-2"></i>
        Tambah Direktorat
    </a>
</div>
@endsection

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Aktif</th>
                    <th>Jumlah Divisi</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($direktorats as $d)
                <tr data-active="{{ $d->aktif ? 1 : 0 }}">
                    <td><span class="fw-semibold">{{ $d->kode }}</span></td>
                    <td>{{ $d->nama }}</td>
                    <td>{!! $d->aktif
                        ? '<span class="badge bg-success text-success-fg badge-pill"><i class="ti ti-check me-1"></i>Ya</span>'
                        : '<span class="badge bg-secondary text-secondary-fg badge-pill"><i class="ti ti-x me-1"></i>Tidak</span>' !!}</td>
                    <td>{{ $d->divisis_count }}</td>
                    <td>
                        <a href="{{ route('master.direktorats.edit', $d) }}" class="btn btn-sm btn-secondary">Edit</a>
                        @if($d->divisis_count == 0)
                        <form method="POST" action="{{ route('master.direktorats.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Hapus direktorat ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($direktorats->hasPages())
    <div class="card-footer d-flex align-items-center">
        <div class="text-muted">
            Menampilkan <strong>{{ $direktorats->firstItem() }}</strong> sampai <strong>{{ $direktorats->lastItem() }}</strong> dari <strong>{{ $direktorats->total() }}</strong> data
        </div>
        {{ $direktorats->appends(request()->query())->links() }}
        <div class="ms-3 d-flex align-items-center">
            <label class="form-label mb-0 me-2" style="white-space:nowrap;">Per halaman</label>
            <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                <option value="{{ request()->fullUrlWithQuery(['per_page' => 25, 'page' => null]) }}" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                <option value="{{ request()->fullUrlWithQuery(['per_page' => 50, 'page' => null]) }}" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="{{ request()->fullUrlWithQuery(['per_page' => 100, 'page' => null]) }}" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('search-direktorat');
    const filterAktif = document.getElementById('filter-aktif');
    const table = document.querySelector('table.card-table');
    if (!search || !table) return;
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const q = search.value.toLowerCase();
        const aktifVal = filterAktif.value;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matchSearch = text.includes(q);
            let matchAktif = true;
            if (aktifVal !== '') {
                matchAktif = row.dataset.active === aktifVal;
            }
            row.style.display = matchSearch && matchAktif ? '' : 'none';
        });
    }

    search.addEventListener('input', filterTable);
    filterAktif.addEventListener('change', filterTable);
});
</script>
@endpush
