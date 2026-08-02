@extends('layouts.app')

@section('title', 'Detail Notulensi Rapat Pimpinan')

@section('content')
@include('arsip.partials._theme')

<div class="simpel-detail">
    <div class="page">

        <div class="breadcrumb"><a href="{{ route('notulensi-rapim.index') }}">Notulensi Rapat Pimpinan</a> / Detail</div>
        <div class="page-head">
            <div>
                <h1>{{ $rapim->judul_rapat }}</h1>
                <div class="badge-row">
                    <span class="badge badge-muted">{{ $rapim->tanggal->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="badge-row">
                @can('update', $rapim)
                <a href="{{ route('notulensi-rapim.edit', $rapim->uuid) }}" class="btn btn-outline-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                    Edit
                </a>
                @endcan
                @can('delete', $rapim)
                <form method="POST" action="{{ route('notulensi-rapim.destroy', $rapim->uuid) }}" class="d-inline" onsubmit="return confirm('Hapus notulensi ini beserta lampiran & fotonya?')">
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
            <div class="card-header"><h3>Detail Notulensi</h3></div>
            <div class="card-body">
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Tanggal</div>
                        <div class="datagrid-value">{{ $rapim->tanggal->format('d/m/Y') }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Dicatat oleh</div>
                        <div class="datagrid-value">{{ $rapim->dicatatOleh?->name }}</div>
                    </div>
                    @if($rapim->daftar_peserta)
                    <div class="datagrid-item span-2">
                        <div class="datagrid-title">Daftar Peserta</div>
                        <div class="datagrid-value" style="white-space:pre-line;">{!! nl2br(e($rapim->daftar_peserta)) !!}</div>
                    </div>
                    @endif
                    <div class="datagrid-item span-2">
                        <div class="datagrid-title">Ringkasan Pembahasan</div>
                        <div class="datagrid-value">{!! $rapim->ringkasan_pembahasan !!}</div>
                    </div>
                    @if($rapim->keputusan_arahan)
                    <div class="datagrid-item span-2">
                        <div class="datagrid-title">Keputusan / Arahan Pimpinan</div>
                        <div class="datagrid-value">{!! $rapim->keputusan_arahan !!}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @include('arsip.partials._lampiran-list', ['rekaman' => $rapim])
        @include('arsip.partials._galeri', ['rekaman' => $rapim])
        @include('arsip.partials._arsip-akses', ['rekaman' => $rapim, 'modul' => 'notulensi-rapim'])
        @include('arsip.partials._riwayat-arsip', ['rekaman' => $rapim])

    </div>
</div>
@endsection
