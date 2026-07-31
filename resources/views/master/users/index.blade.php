@extends('layouts.app')

@section('page-title', 'Master User')

@section('page-header')
@endsection

@section('page-actions')
<div class="d-flex gap-2 align-items-center">
    <div class="input-icon">
        <input type="text" id="search-users" class="form-control form-control-sm" placeholder="Cari nama, email..." style="width: 220px;">
        <span class="input-icon-addon">
            <i class="ti ti-search"></i>
        </span>
    </div>
    <select id="filter-role" class="form-select form-select-sm" style="width: 140px;">
        <option value="">Semua Role</option>
        @foreach($roles as $r)
        <option value="{{ $r->id }}">{{ $r->label }}</option>
        @endforeach
    </select>
    <select id="filter-direktorat" class="form-select form-select-sm" style="width: 160px;">
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
    <a href="{{ route('master.users.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-2"></i>
        Tambah User
    </a>
</div>
@endsection

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Direktorat</th>
                    <th>Divisi</th>
                    <th>Departemen</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr data-active="{{ $u->is_active ? 1 : 0 }}">
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->direktorat?->nama ?? '-' }}</td>
                    <td>{{ $u->divisi?->nama ?? '-' }}</td>
                    <td>{{ $u->departemen?->nama ?? '-' }}</td>
                    <td>{{ $u->role?->label ?? '-' }}</td>
                    <td>{!! $u->is_active
                        ? '<span class="badge bg-success text-success-fg badge-pill"><i class="ti ti-check me-1"></i>Aktif</span>'
                        : '<span class="badge bg-secondary text-secondary-fg badge-pill"><i class="ti ti-x me-1"></i>Nonaktif</span>'
                    !!}</td>
                    <td>
                        <div class="btn-group dropdown">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="dropdown">Actions</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('master.users.edit', $u) }}">Edit</a>
                                @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('master.users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Nonaktifkan user ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">Nonaktifkan</button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer d-flex align-items-center">
        <div class="text-muted">
            Menampilkan <strong>{{ $users->firstItem() }}</strong> sampai <strong>{{ $users->lastItem() }}</strong> dari <strong>{{ $users->total() }}</strong> data
        </div>
        <ul class="pagination ms-auto mb-0">
            {{ $users->app(request()->query())->links() }}
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
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('search-users');
    const filterRole = document.getElementById('filter-role');
    const filterDirektorat = document.getElementById('filter-direktorat');
    const filterAktif = document.getElementById('filter-aktif');
    const table = document.querySelector('table.card-table');
    if (!search || !table) return;
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const q = search.value.toLowerCase();
        const roleVal = filterRole.value;
        const dirVal = filterDirektorat.value;
        const aktifVal = filterAktif.value;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matchSearch = text.includes(q);
            const roleCell = row.cells[5]?.textContent.trim();
            const dirCell = row.cells[2]?.textContent.trim();
            const matchRole = !roleVal || roleCell === filterRole.options[filterRole.selectedIndex].text;
            const matchDir = !dirVal || dirCell === filterDirektorat.options[filterDirektorat.selectedIndex].text;
            let matchAktif = true;
            if (aktifVal !== '') {
                matchAktif = row.dataset.active === aktifVal;
            }
            row.style.display = matchSearch && matchRole && matchDir && matchAktif ? '' : 'none';
        });
    }

    search.addEventListener('input', filterTable);
    filterRole.addEventListener('change', filterTable);
    filterDirektorat.addEventListener('change', filterTable);
    filterAktif.addEventListener('change', filterTable);
});
</script>
@endpush
