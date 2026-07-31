@extends('layouts.app')

@section('page-title', 'Master Role')

@section('page-header')
@endsection

@section('page-actions')
<a href="{{ route('master.roles.create') }}" class="btn btn-primary">Tambah Role</a>
@endsection

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Label</th>
                    <th>Jumlah User</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $r)
                <tr>
                    <td>{{ $r->kode }}</td>
                    <td>{{ $r->label }}</td>
                    <td>{{ $r->users_count }}</td>
                    <td>
                        <a href="{{ route('master.roles.edit', $r) }}" class="btn btn-sm btn-secondary">Edit</a>
                        @if($r->users_count == 0)
                        <form method="POST" action="{{ route('master.roles.destroy', $r) }}" class="d-inline" onsubmit="return confirm('Hapus role ini?')">
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
    @if($roles->hasPages())
    <div class="card-footer d-flex align-items-center">
        <div class="text-muted">
            Menampilkan <strong>{{ $roles->firstItem() }}</strong> sampai <strong>{{ $roles->lastItem() }}</strong> dari <strong>{{ $roles->total() }}</strong> data
        </div>
        <ul class="pagination ms-auto mb-0">
            {{ $roles->app(request()->query())->links() }}
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
