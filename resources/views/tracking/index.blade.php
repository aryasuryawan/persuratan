@extends('layouts.app')

@section('page-title', 'Tracking Surat')

@section('page-header')
@endsection

@section('content')
<div class="row row-cards row-deck mb-3">
    @foreach($stats as $stat)
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card stat-{{ $stat['color'] ?? 'primary' }}">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="subheader">{{ $stat['label'] }}</div>
                        <div class="h1 mt-1 mb-0">{{ $stat['count'] }}</div>
                    </div>
                    <div class="stat-icon bg-{{ $stat['color'] ?? 'primary' }}-subtle">
                        <i class="ti ti-{{ $stat['icon'] ?? 'mail' }} fs-2"></i>
                    </div>
                </div>
                <div class="text-muted text-sm mt-2">
                    {{ $stat['description'] ?? strtolower($stat['label']) }}
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row row-deck">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Tren Surat Masuk (30 hari terakhir)</h3>
            </div>
            <div id="chart-tren" style="height: 300px;" data-url="{{ route('api.tracking.chart') }}"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Cari Surat</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('tracking.index') }}">
                    <div class="input-group">
                        <input type="text" name="surat" class="form-control" placeholder="Masukkan UUID atau No. Agenda surat..." value="{{ request('surat') }}">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </form>
            </div>
        </div>

        @if($surat)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Surat</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted fw-semibold" style="width:120px">No. Agenda</td>
                        <td><span class="fw-semibold">{{ $surat->nomor_agenda }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Pengirim</td>
                        <td>{{ $surat->pengirim }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Perihal</td>
                        <td>{{ $surat->perihal }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Tgl Surat</td>
                        <td>{{ $surat->tanggal_surat ? $surat->tanggal_surat->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Tgl Diterima</td>
                        <td>{{ $surat->tanggal_diterima->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Status</td>
                        <td><x-badge-status :status="$surat->status->kode" /></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Klasifikasi</td>
                        <td><x-badge-klasifikasi :klasifikasi="$surat->klasifikasi" /></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Riwayat Status</h3>
            </div>
            <div class="card-body">
                <ul class="timeline">
                    @forelse($riwayat as $r)
                        <x-timeline-item :riwayat="$r" />
                    @empty
                        <li class="timeline-event">
                            <div class="card timeline-event-card">
                                <div class="card-body text-muted">Belum ada riwayat.</div>
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
