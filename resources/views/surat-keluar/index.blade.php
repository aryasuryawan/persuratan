@extends('layouts.app')

@section('page-title', 'Surat Keluar')

@section('page-header')
@endsection

@section('page-actions')
<a href="{{ route('surat-keluar.create') }}" class="btn btn-primary">
    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
    Tambah Surat Keluar
</a>
@endsection

@section('content')
<div class="card">
    <form method="GET" class="card-body border-bottom py-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="input-icon flex-grow-1" style="min-width: 200px; max-width: 340px;">
                <input type="text" class="form-control" name="q" placeholder="Cari no surat, perihal, tujuan..." value="{{ request('q') }}">
                <span class="input-icon-addon"><i class="ti ti-search"></i></span>
            </div>
            <select class="form-select" name="klasifikasi_id" style="width: 200px;" title="Klasifikasi">
                <option value="">Semua Klasifikasi</option>
                @foreach($klasifikasi as $k)
                <option value="{{ $k->id }}" {{ request('klasifikasi_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                @endforeach
            </select>
            <select class="form-select" name="kode_surat_id" style="width: 150px;" title="Kode Surat">
                <option value="">Semua Kode</option>
                @foreach($kodeSurats as $k)
                <option value="{{ $k->id }}" {{ request('kode_surat_id') == $k->id ? 'selected' : '' }}>{{ $k->kode }}</option>
                @endforeach
            </select>
            <input type="date" class="form-control" name="tgl_awal" value="{{ request('tgl_awal') }}" title="Tanggal awal" style="width: 150px;">
            <input type="date" class="form-control" name="tgl_akhir" value="{{ request('tgl_akhir') }}" title="Tanggal akhir" style="width: 150px;">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('surat-keluar.index') }}" class="btn btn-link link-secondary">Reset</a>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>No. Surat</th>
                    <th>Kode</th>
                    <th>Tujuan</th>
                    <th>Perihal</th>
                    <th>Tgl Surat</th>
                    <th>Klasifikasi</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($surat as $s)
                <tr>
                    <td>{{ $s->nomor_surat }}</td>
                    <td>{{ $s->kodeSurat?->kode ?? '-' }}</td>
                    <td>{{ $s->tujuan_surat }}</td>
                    <td>{{ $s->perihal }}</td>
                    <td>{{ $s->tanggal_surat->format('d/m/Y') }}</td>
                    <td><x-badge-klasifikasi :klasifikasi="$s->klasifikasi" /></td>
                    <td>
                        <a href="{{ route('surat-keluar.show', $s->uuid) }}" class="btn btn-sm btn-secondary">Detail</a>
                        <a href="{{ route('surat-keluar.edit', $s->uuid) }}" class="btn btn-sm btn-secondary">Edit</a>
                        <form method="POST" action="{{ route('surat-keluar.destroy', $s->uuid) }}" class="d-inline" onsubmit="return confirm('Hapus surat keluar ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Belum ada surat keluar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($surat->hasPages())
    <div class="card-footer d-flex align-items-center">
        <div class="text-muted">
            Menampilkan <strong>{{ $surat->firstItem() }}</strong> sampai <strong>{{ $surat->lastItem() }}</strong> dari <strong>{{ $surat->total() }}</strong> data
        </div>
        {{ $surat->appends(request()->query())->links() }}
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
