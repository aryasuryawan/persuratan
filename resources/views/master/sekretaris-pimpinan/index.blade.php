@extends('layouts.app')

@section('page-title', 'Relasi Sekretaris-Pimpinan')

@section('page-header')
@endsection

@section('page-actions')
<a href="{{ route('master.sekretaris-pimpinan.create') }}" class="btn btn-primary">Tambah Relasi</a>
@endsection

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Sekretaris</th>
                    <th>Pimpinan</th>
                    <th>Aktif</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $d)
                <tr>
                    <td>{{ $d->sekretaris->name }}</td>
                    <td>{{ $d->pimpinan->name }}</td>
                    <td>
                        {!! $d->is_active
                            ? '<span class="badge bg-success text-success-fg badge-pill"><i class="ti ti-check me-1"></i>Aktif</span>'
                            : '<span class="badge bg-secondary text-secondary-fg badge-pill"><i class="ti ti-x me-1"></i>Nonaktif</span>'
                        !!}
                    </td>
                    <td>
                        <form method="POST" action="{{ route('master.sekretaris-pimpinan.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Hapus relasi?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if(count($data) == 0)
                <tr>
                    <td colspan="4" class="text-center text-muted">Belum ada relasi.</td>
                </tr>
                @endif
            </tbody>
        </table>
    @if($data->hasPages())
    <div class="card-footer d-flex align-items-center">
        <div class="text-muted">
            Menampilkan <strong>{{ $data->firstItem() }}</strong> sampai <strong>{{ $data->lastItem() }}</strong> dari <strong>{{ $data->total() }}</strong> data
        </div>
        {{ $data->appends(request()->query())->links() }}
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
</div>
@endsection
