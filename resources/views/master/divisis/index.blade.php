@extends('layouts.app')

@section('page-title', 'Master Divisi')

@section('page-header')
@endsection

@section('page-actions')
<div class="d-flex gap-2 align-items-center">
    <div class="input-icon">
        <input type="text" id="search-divisi" class="form-control form-control-sm" placeholder="Cari kode, nama..." style="width: 220px;">
        <span class="input-icon-addon">
            <i class="ti ti-search"></i>
        </span>
    </div>
    <select id="filter-direktorat" class="form-select form-select-sm" style="width: 180px;">
        <option value="">Semua Direktorat</option>
        @foreach($direktorats as $d)
        <option value="{{ $d->id }}">{{ $d->nama }}</option>
        @endforeach
    </select>
    <select id="filter-aktif" class="form-select form-select-sm" style="width: 120px;">
        <option value="">Semua</option>
        <option value="1">Aktif</option>
        <option value="0">Nonaktif</option>
    </select>
    <a href="{{ route('master.divisis.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-2"></i>
        Tambah Divisi
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
                    <th>Direktorat</th>
                    <th>Aktif</th>
                    <th>Jumlah Dept</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($divisis as $d)
                <tr data-active="{{ $d->aktif ? 1 : 0 }}">
                    <td><span class="fw-semibold">{{ $d->kode }}</span></td>
                    <td>{{ $d->nama }}</td>
                    <td>{{ $d->direktorat->nama }}</td>
                    <td>{!! $d->aktif
                        ? '<span class="badge bg-success text-success-fg badge-pill"><i class="ti ti-check me-1"></i>Ya</span>'
                        : '<span class="badge bg-secondary text-secondary-fg badge-pill"><i class="ti ti-x me-1"></i>Tidak</span>' !!}</td>
                    <td>{{ $d->departemens_count }}</td>
                    <td>
                        <a href="{{ route('master.divisis.edit', $d) }}" class="btn btn-sm btn-secondary">Edit</a>
                        @if($d->departemens_count == 0)
                        <form method="POST" action="{{ route('master.divisis.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Hapus divisi ini?')">
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
    @if($divisis->hasPages())
    <div class="card-footer d-flex align-items-center">
        <div class="text-muted">
            Menampilkan <strong>{{ $divisis->firstItem() }}</strong> sampai <strong>{{ $divisis->lastItem() }}</strong> dari <strong>{{ $divisis->total() }}</strong> data
        </div>
        <ul class="pagination ms-auto mb-0">
            {{ $divisis->app(request()->query())->links() }}
        </ul>
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
    const search = document.getElementById('search-divisi');
    const filterDirektorat = document.getElementById('filter-direktorat');
    const filterAktif = document.getElementById('filter-aktif');
    const table = document.querySelector('table.card-table');
    if (!search || !table) return;
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const q = search.value.toLowerCase();
        const dirVal = filterDirektorat.value;
        const aktifVal = filterAktif.value;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matchSearch = text.includes(q);
            const dirCell = row.cells[2]?.textContent.trim();
            const matchDir = !dirVal || dirCell === filterDirektorat.options[filterDirektorat.selectedIndex].text;
            let matchAktif = true;
            if (aktifVal !== '') {
                matchAktif = row.dataset.active === aktifVal;
            }
            row.style.display = matchSearch && matchDir && matchAktif ? '' : 'none';
        });
    }

    search.addEventListener('input', filterTable);
    filterDirektorat.addEventListener('change', filterTable);
    filterAktif.addEventListener('change', filterTable);
});
</script>
@endpush
