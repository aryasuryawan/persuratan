@extends('layouts.app')

@section('title', 'Detail Hasil Komite Persetujuan')

@section('content')
@php
    $jenisBadge = $komite->jenis === 'pembelian' ? 'badge-muted' : 'badge-warning';
    $hasilBadge = match ($komite->hasil) {
        'disetujui' => 'badge-success',
        'ditolak' => 'badge-danger',
        'revisi' => 'badge-warning',
        'ditunda' => 'badge-muted',
        default => 'badge-muted',
    };
@endphp

@include('arsip.partials._theme')

<div class="simpel-detail">
    <div class="page">

        <div class="breadcrumb"><a href="{{ route('komite-persetujuan.index') }}">Hasil Komite Persetujuan</a> / Detail</div>
        <div class="page-head">
            <div>
                <h1>{{ $komite->judul }}</h1>
                <div class="badge-row">
                    <span class="badge {{ $jenisBadge }}">{{ $komite->jenis === 'pembelian' ? 'Pembelian' : 'Prinsip Proyek' }}</span>
                    <span class="badge {{ $hasilBadge }} text-capitalize">{{ $komite->hasil }}</span>
                    @if($komite->is_rahasia)
                    <span class="badge badge-warning">Rahasia</span>
                    @endif
                    <span class="badge badge-muted">{{ $komite->tanggal_rapat->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="badge-row">
                @can('update', $komite)
                <a href="{{ route('komite-persetujuan.edit', $komite->uuid) }}" class="btn btn-outline-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                    Edit
                </a>
                @endcan
                @can('delete', $komite)
                <form method="POST" action="{{ route('komite-persetujuan.destroy', $komite->uuid) }}" class="d-inline" onsubmit="return confirm('Hapus hasil komite ini beserta lampiran & fotonya?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Hapus
                    </button>
                </form>
                @endcan
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Informasi Komite</h3></div>
            <div class="card-body">
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Jenis</div>
                        <div class="datagrid-value"><span class="badge {{ $jenisBadge }}">{{ $komite->jenis === 'pembelian' ? 'Pembelian' : 'Prinsip Proyek' }}</span></div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Tanggal Rapat</div>
                        <div class="datagrid-value">{{ $komite->tanggal_rapat->format('d/m/Y') }}</div>
                    </div>
                    @if($komite->nilai_pembelian !== null)
                    <div class="datagrid-item">
                        <div class="datagrid-title">Nilai Pembelian</div>
                        <div class="datagrid-value">Rp {{ number_format($komite->nilai_pembelian, 2, ',', '.') }}</div>
                    </div>
                    @endif
                    <div class="datagrid-item">
                        <div class="datagrid-title">Hasil</div>
                        <div class="datagrid-value"><span class="badge {{ $hasilBadge }} text-capitalize">{{ $komite->hasil }}</span></div>
                    </div>
                    @if($komite->daftar_peserta)
                    <div class="datagrid-item span-2">
                        <div class="datagrid-title">Daftar Peserta</div>
                        <div class="datagrid-value" style="white-space:pre-line;">{!! nl2br(e($komite->daftar_peserta)) !!}</div>
                    </div>
                    @endif
                    @if($komite->catatan_keputusan)
                    <div class="datagrid-item span-2">
                        <div class="datagrid-title">Catatan Keputusan</div>
                        <div class="datagrid-value">{{ $komite->catatan_keputusan }}</div>
                    </div>
                    @endif
                    <div class="datagrid-item">
                        <div class="datagrid-title">Dicatat oleh</div>
                        <div class="datagrid-value">{{ $komite->dicatatOleh?->name }}</div>
                    </div>
                </div>
            </div>
        </div>

        @include('arsip.partials._lampiran-list', ['rekaman' => $komite])
        @include('arsip.partials._galeri', ['rekaman' => $komite])
        @include('arsip.partials._arsip-akses', ['rekaman' => $komite, 'modul' => 'komite-persetujuan'])
        @include('arsip.partials._riwayat-arsip', ['rekaman' => $komite])

    </div>
</div>
@endsection
