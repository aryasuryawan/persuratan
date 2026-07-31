@extends('layouts.app')

@section('title', 'Surat Keputusan')

@section('content')
@include('arsip.partials._theme')

<div class="simpel-detail">
    <div class="page">

        <div class="breadcrumb"><a href="{{ route('dashboard') }}">Beranda</a> / Surat Keputusan</div>
        <div class="page-head">
            <div>
                <h1>Surat Keputusan</h1>
                <div class="badge-row">
                    <span class="badge badge-muted"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/></svg>{{ $arsip->total() }} dokumen</span>
                </div>
            </div>
            @can('create', App\Models\SuratKeputusan::class)
            <a href="{{ route('surat-keputusan.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Catat SK
            </a>
            @endcan
        </div>

        <div class="card">
            <form method="GET" class="card-body">
                <div class="filter-bar">
                    <div class="input-icon flex-grow-1" style="min-width: 200px; max-width: 340px;">
                        <input type="text" class="form-control" name="q" placeholder="Cari nomor SK, perihal, kategori..." value="{{ request('q') }}">
                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                    </div>
                    <select class="form-select" name="tahun" style="width: 140px;" title="Tahun">
                        <option value="">Semua Tahun</option>
                        @foreach($tahunList as $tahun)
                        <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
                        Cari
                    </button>
                    <a href="{{ route('surat-keputusan.index') }}" class="btn btn-ghost">Reset</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. SK</th>
                            <th>Perihal</th>
                            <th>Kategori</th>
                            <th>Tgl SK</th>
                            <th>Status</th>
                            <th class="text-center">Lampiran</th>
                            <th class="text-center">Foto</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arsip as $item)
                        <tr>
                            <td>{{ $item->nomor_sk }}</td>
                            <td><a href="{{ route('surat-keputusan.show', $item->uuid) }}">{{ $item->perihal }}</a></td>
                            <td>{{ $item->kategori ?? '-' }}</td>
                            <td>{{ $item->tanggal_sk->format('d/m/Y') }}</td>
                            <td>
                                @if($item->status === 'aktif')
                                <span class="badge badge-success">Aktif</span>
                                @else
                                <span class="badge badge-muted">Tidak Berlaku</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->lampiran_count }}</td>
                            <td class="text-center">{{ $item->foto_kegiatan_count }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('surat-keputusan.show', $item->uuid) }}" class="btn btn-sm">Lihat</a>
                                    @can('update', $item)
                                    <a href="{{ route('surat-keputusan.edit', $item->uuid) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    @endcan
                                    @can('delete', $item)
                                    <form method="POST" action="{{ route('surat-keputusan.destroy', $item->uuid) }}" class="d-inline" onsubmit="return confirm('Hapus SK ini beserta lampiran & fotonya?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center" style="color:var(--muted);">Belum ada Surat Keputusan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($arsip->hasPages())
            <div class="card-body" style="display:flex; align-items:center; gap:16px; border-top:1px solid var(--border);">
                <div class="muted-count">
                    Menampilkan <strong>{{ $arsip->firstItem() }}</strong> sampai <strong>{{ $arsip->lastItem() }}</strong> dari <strong>{{ $arsip->total() }}</strong> data
                </div>
                <ul class="pagination ms-auto mb-0">
                    {{ $arsip->app(request()->query())->links() }}
                </ul>
                <div class="d-flex align-items-center">
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
</div>
@endsection
