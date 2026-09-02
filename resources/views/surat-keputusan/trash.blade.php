@extends('layouts.tabler')

@section('title', 'Sampah - Surat Keputusan')

@section('content')
<div class="page-header d-print-none">
    <div class="page-pretitle">Arsip</div>
    <h2 class="page-title">Sampah - Surat Keputusan</h2>
</div>

<div class="page-body">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="ti ti-trash me-2"></i>Surat Keputusan yang Dihapus
            </div>
            <div class="card-actions">
                <a href="{{ route('surat-keputusan.index') }}" class="btn btn-ghost-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($items->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No. SK</th>
                            <th>Perihal</th>
                            <th>Ditetapkan Oleh</th>
                            <th>Tanggal SK</th>
                            <th>Dihapus Pada</th>
                            <th class="w-1">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $item->nomor_sk }}</div>
                            </td>
                            <td>{{ $item->perihal }}</td>
                            <td>{{ $item->ditetapkan_oleh }}</td>
                            <td>{{ $item->tanggal_sk?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <span class="text-muted">{{ $item->deleted_at?->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <div class="btn-list justify-content-end">
                                    <form method="POST" action="{{ route('surat-keputusan.restore', $item->uuid) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Pulihkan">
                                            <i class="ti ti-refresh me-1"></i> Pulihkan
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('surat-keputusan.force-delete', $item->uuid) }}" class="d-inline" onsubmit="return confirm('Hapus permanen surat keputusan ini? Data tidak bisa dipulihkan!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus Permanen">
                                            <i class="ti ti-trash me-1"></i> Hapus Permanen
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $items->links() }}
            @else
            <div class="empty">
                <div class="empty-icon">
                    <i class="ti ti-trash"></i>
                </div>
                <p class="empty-title">Sampah kosong</p>
                <p class="empty-subtitle text-secondary">Tidak ada surat keputusan yang dihapus.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
