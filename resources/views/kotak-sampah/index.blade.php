@extends('layouts.app')

@section('title', 'Kotak Sampah')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="row g-2 align-items-center">
        <div class="col">
            <div class="page-pretitle">SiMPEL</div>
            <h2 class="page-title">Kotak Sampah</h2>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'semua' ? 'active' : '' }}" href="{{ route('kotak-sampah.index', ['tab' => 'semua']) }}">Semua</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'surat-masuk' ? 'active' : '' }}" href="{{ route('kotak-sampah.index', ['tab' => 'surat-masuk']) }}">Surat Masuk</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'surat-keluar' ? 'active' : '' }}" href="{{ route('kotak-sampah.index', ['tab' => 'surat-keluar']) }}">Surat Keluar</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'surat-keputusan' ? 'active' : '' }}" href="{{ route('kotak-sampah.index', ['tab' => 'surat-keputusan']) }}">SK</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'komite' ? 'active' : '' }}" href="{{ route('kotak-sampah.index', ['tab' => 'komite']) }}">Komite</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'notulensi' ? 'active' : '' }}" href="{{ route('kotak-sampah.index', ['tab' => 'notulensi']) }}">Notulensi</a>
    </li>
</ul>

<div class="card card-bordered">
    <div class="card-header">
        <h3 class="card-title">Data yang Dihapus</h3>
        <div class="card-actions">
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="input-icon">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari..." value="{{ request('q') }}">
                    <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Cari</button>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-vcenter card-table">
            <thead>
                <tr>
                    <th>Jenis</th>
                    <th>Judul</th>
                    <th>Perihal / Detail</th>
                    <th>Dihapus Pada</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>
                        <span class="badge bg-secondary-lt text-secondary">{{ $item['type'] }}</span>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $item['judul'] }}</div>
                    </td>
                    <td class="text-secondary">{{ $item['perihal'] }}</td>
                    <td>
                        <span class="text-muted">{{ $item['deleted_at']?->format('d/m/Y H:i') }}</span>
                    </td>
                    <td>
                        <div class="btn-list justify-content-end">
                            <form method="POST" action="{{ $item['restore_route'] }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Pulihkan">
                                    <i class="ti ti-refresh me-1"></i> Pulihkan
                                </button>
                            </form>
                            <form method="POST" action="{{ $item['force_delete_route'] }}" class="d-inline" onsubmit="return confirm('Hapus permanen? Data tidak bisa dipulihkan!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus Permanen">
                                    <i class="ti ti-trash me-1"></i> Hapus Permanen
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-trash"></i></div>
                            <p class="empty-title">Sampah kosong</p>
                            <p class="empty-subtitle text-secondary">Tidak ada data yang dihapus.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
