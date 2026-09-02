@extends('layouts.app')

@section('page-title', 'Master Klasifikasi')
@section('page-subtitle', 'Kelola klasifikasi surat')

@section('page-header')
@endsection

@section('page-actions')
<div class="d-flex gap-2 align-items-center">
    <div class="input-icon">
        <input type="text" id="search-klasifikasi" class="form-control form-control-sm" placeholder="Cari kode, nama..." style="width: 240px;">
        <span class="input-icon-addon">
            <i class="ti ti-search"></i>
        </span>
    </div>
    <select id="filter-rahasia" class="form-select form-select-sm" style="width: 160px;">
        <option value="">Semua</option>
        <option value="1">🔒 Rahasia</option>
        <option value="0">🌍 Umum</option>
    </select>
    <a href="{{ route('master.klasifikasi.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-2"></i>
        Tambah Klasifikasi
    </a>
</div>
@endsection

@section('content')
<div class="card card-bordered table-card">
    <div class="table-responsive">
        <table class="table table-hover table-vcenter card-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Jumlah Surat</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($klasifikasi as $k)
                <tr>
                    <td>{{ $k->kode }}</td>
                    <td>{{ $k->nama }}</td>
                    <td>
                        @if($k->is_rahasia)
                            <span class="badge bg-amber-lt">
                                <i class="ti ti-lock me-1"></i> Rahasia
                            </span>
                        @else
                            <span class="badge bg-green-lt">
                                <i class="ti ti-world me-1"></i> Umum
                            </span>
                        @endif
                    </td>
                    <td>{{ $k->surat_masuk_count }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('master.klasifikasi.edit', $k) }}" class="btn btn-sm btn-secondary">
                                <i class="ti ti-edit me-1"></i> Edit
                            </a>
                            @if($k->surat_masuk_count == 0)
                            <form method="POST" action="{{ route('master.klasifikasi.destroy', $k) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="ti ti-trash me-1"></i> Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($klasifikasi->hasPages())
    <div class="card-footer d-flex align-items-center">
        <div class="text-muted">
            Menampilkan <strong>{{ $klasifikasi->firstItem() }}</strong> sampai <strong>{{ $klasifikasi->lastItem() }}</strong> dari <strong>{{ $klasifikasi->total() }}</strong> data
        </div>
        {{ $klasifikasi->appends(request()->query())->links() }}
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
    const search = document.getElementById('search-klasifikasi');
    const filterRahasia = document.getElementById('filter-rahasia');
    const table = document.querySelector('table.card-table');
    if (!search || !table) return;
    const rows = table.querySelectorAll('tbody tr');
    
    function filterTable() {
        const q = search.value.toLowerCase();
        const rahasiaFilter = filterRahasia.value;
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const isRahasia = row.cells[2].textContent.includes('Rahasia');
            const matchSearch = text.includes(q);
            const matchRahasia = rahasiaFilter === '' || (rahasiaFilter === '1' && isRahasia) || (rahasiaFilter === '0' && !isRahasia);
            row.style.display = matchSearch && matchRahasia ? '' : 'none';
        });
    }
    
    search.addEventListener('input', filterTable);
    filterRahasia.addEventListener('change', filterTable);
});
</script>
@endpush
