@extends('layouts.app')

@section('title', 'Detail Surat Keputusan')

@section('content')
@php
    $statusBadge = $sk->status === 'aktif' ? 'badge-success' : 'badge-muted';
    $statusLabel = $sk->status === 'aktif' ? 'Aktif' : 'Tidak Berlaku';
@endphp

@include('arsip.partials._theme')

<div class="simpel-detail">
    <div class="page">

        <div class="breadcrumb"><a href="{{ route('surat-keputusan.index') }}">Surat Keputusan</a> / Detail</div>
        <div class="page-head">
            <div>
                <h1>{{ $sk->perihal }}</h1>
                <div class="badge-row">
                    <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                    @if($sk->is_rahasia)
                    <span class="badge badge-warning">Rahasia</span>
                    @endif
                    @if($sk->kategori)
                    <span class="badge badge-muted">{{ $sk->kategori }}</span>
                    @endif
                    <span class="badge badge-muted">{{ $sk->tanggal_sk->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="badge-row">
                @can('update', $sk)
                <a href="{{ route('surat-keputusan.edit', $sk->uuid) }}" class="btn btn-outline-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                    Edit
                </a>
                @endcan
                @can('delete', $sk)
                <form method="POST" action="{{ route('surat-keputusan.destroy', $sk->uuid) }}" class="d-inline" onsubmit="return confirm('Hapus SK ini beserta lampiran & fotonya?')">
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
            <div class="card-header"><h3>Detail SK</h3></div>
            <div class="card-body">
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Nomor SK</div>
                        <div class="datagrid-value">{{ $sk->nomor_sk }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Kategori</div>
                        <div class="datagrid-value">{{ $sk->kategori ?? '-' }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Tanggal SK</div>
                        <div class="datagrid-value">{{ $sk->tanggal_sk->format('d/m/Y') }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Tanggal Berlaku</div>
                        <div class="datagrid-value">{{ $sk->tanggal_berlaku?->format('d/m/Y') ?? '-' }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Ditetapkan Oleh</div>
                        <div class="datagrid-value">{{ $sk->ditetapkan_oleh ?? '-' }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Status</div>
                        <div class="datagrid-value"><span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span></div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Dicatat oleh</div>
                        <div class="datagrid-value">{{ $sk->dicatatOleh?->name }}</div>
                    </div>
                    @if($sk->ringkasan)
                    <div class="datagrid-item span-2">
                        <div class="datagrid-title">Ringkasan</div>
                        <div class="datagrid-value">{{ $sk->ringkasan }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @include('arsip.partials._lampiran-list', ['rekaman' => $sk])
        @include('arsip.partials._galeri', ['rekaman' => $sk])
        @include('arsip.partials._arsip-akses', ['rekaman' => $sk, 'modul' => 'surat-keputusan'])
        @include('arsip.partials._riwayat-arsip', ['rekaman' => $sk])

    </div>
</div>
@endsection
