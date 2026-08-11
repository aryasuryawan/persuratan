@extends('layouts.app')

@section('page-title', 'Verifikasi Surat')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>No. Agenda</th>
                    <th>Pengirim</th>
                    <th>Perihal</th>
                    <th>Tgl Diterima</th>
                    <th>Klasifikasi</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($surat as $s)
                <tr>
                    <td>{{ $s->nomor_agenda }}</td>
                    <td>{{ $s->pengirim }}</td>
                    <td>{{ $s->perihal }}</td>
                    <td>{{ $s->tanggal_diterima->format('d/m/Y') }}</td>
                    <td><x-badge-klasifikasi :klasifikasi="$s->klasifikasi" /></td>
                    <td>
                        <form method="POST" action="{{ route('verifikasi-surat.verify') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="surat_id" value="{{ $s->id }}">
                            <button type="submit" class="btn btn-sm btn-info">Verifikasi</button>
                        </form>
                        <a href="{{ route('penerimaan-surat.show', $s->uuid) }}" class="btn btn-sm btn-secondary">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Tidak ada surat yang perlu diverifikasi.</td>
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
