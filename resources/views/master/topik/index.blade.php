@extends('layouts.app')

@section('page-title', 'Master Topik')
@section('page-subtitle', 'Kelola topik/subjek surat')

@section('page-header')
@endsection

@section('page-actions')
<div class="d-flex gap-2 align-items-center">
    <div class="input-icon">
        <input type="text" id="search-topik" class="form-control form-control-sm" placeholder="Cari kode, nama..." style="width: 240px;">
        <span class="input-icon-addon">
            <i class="ti ti-search"></i>
        </span>
    </div>
    <a href="{{ route('master.topik.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-2"></i>
        Tambah Topik
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
                    <th>Warna</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($topik as $t)
                <tr>
                    <td>{{ $t->kode }}</td>
                    <td>{{ $t->nama }}</td>
                    <td><span class="badge bg-{{ $t->color }}-lt text-{{ $t->color }}">{{ $t->nama }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('master.topik.edit', $t) }}" class="btn btn-sm btn-secondary">
                                <i class="ti ti-edit me-1"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('master.topik.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="ti ti-trash me-1"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($topik->hasPages())
    <div class="card-footer d-flex align-items-center">
        <div class="text-muted">
            Menampilkan <strong>{{ $topik->firstItem() }}</strong> sampai <strong>{{ $topik->lastItem() }}</strong> dari <strong>{{ $topik->total() }}</strong> data
        </div>
        {{ $topik->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('search-topik');
    const table = document.querySelector('table.card-table');
    if (!search || !table) return;
    const rows = table.querySelectorAll('tbody tr');
    
    search.addEventListener('input', () => {
        const q = search.value.toLowerCase();
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    });
});
</script>
@endpush
