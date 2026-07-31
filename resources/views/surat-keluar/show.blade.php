@extends('layouts.app')

@section('page-title', 'Detail Surat Keluar')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $surat->perihal }}</h3>
        <div class="card-actions">
            <a href="{{ route('surat-keluar.edit', $surat->uuid) }}" class="btn btn-primary">
                <i class="ti ti-edit me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('surat-keluar.destroy', $surat->uuid) }}" class="d-inline" onsubmit="return confirm('Hapus surat keluar ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="ti ti-trash me-1"></i>Hapus
                </button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-borderless">
            <tr>
                <td class="text-muted fw-semibold" style="width:180px">No. Surat</td>
                <td>{{ $surat->nomor_surat }}</td>
            </tr>
            <tr>
                <td class="text-muted fw-semibold">Tujuan</td>
                <td>{{ $surat->tujuan_surat }}</td>
            </tr>
            <tr>
                <td class="text-muted fw-semibold">Perihal</td>
                <td>{{ $surat->perihal }}</td>
            </tr>
            <tr>
                <td class="text-muted fw-semibold">Tanggal Surat</td>
                <td>{{ $surat->tanggal_surat->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="text-muted fw-semibold">Klasifikasi</td>
                <td><x-badge-klasifikasi :klasifikasi="$surat->klasifikasi" /></td>
            </tr>
            @if($surat->kodeSurat)
            <tr>
                <td class="text-muted fw-semibold">Kode Surat</td>
                <td><span class="fw-semibold">{{ $surat->kodeSurat->kode }}</span> — {{ $surat->kodeSurat->nama }}</td>
            </tr>
            @endif
            @if($surat->suratMasuk)
            <tr>
                <td class="text-muted fw-semibold">Terkait Surat Masuk</td>
                <td><a href="{{ route('penerimaan-surat.show', $surat->suratMasuk->uuid) }}">{{ $surat->suratMasuk->nomor_agenda }}</a></td>
            </tr>
            @endif
            <tr>
                <td class="text-muted fw-semibold">Dicatat oleh</td>
                <td>{{ $surat->pembuat->name }}</td>
            </tr>
            @if($surat->lampiran)
            <tr>
                <td class="text-muted fw-semibold">Lampiran</td>
                <td>
                    <ul class="list-unstyled mb-0">
                        @foreach($surat->lampiran as $file)
                        <li class="mb-1">
                            <a href="{{ \Storage::disk('public')->url($file['stored_path']) }}" target="_blank" class="d-flex align-items-center text-break">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/></svg>
                                {{ $file['original_name'] }}
                                <small class="text-muted ms-2">({{ number_format($file['file_size'] / 1024, 1) }} KB)</small>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @else
            <tr>
                <td class="text-muted fw-semibold">Lampiran</td>
                <td class="text-muted"><em>Tidak ada lampiran</em></td>
            </tr>
            @endif
        </table>
    </div>
</div>
@endsection
