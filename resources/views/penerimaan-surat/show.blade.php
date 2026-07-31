@extends('layouts.app')

@section('page-title', 'Detail Surat Masuk')

@section('page-header')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $surat->perihal }}</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted fw-semibold" style="width:180px">No. Agenda</td>
                        <td>{{ $surat->nomor_agenda }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">No. Surat</td>
                        <td>{{ $surat->nomor_surat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Pengirim</td>
                        <td>{{ $surat->pengirim }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Tanggal Surat</td>
                        <td>{{ $surat->tanggal_surat ? $surat->tanggal_surat->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Tanggal Diterima</td>
                        <td>{{ $surat->tanggal_diterima->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Klasifikasi</td>
                        <td><x-badge-klasifikasi :klasifikasi="$surat->klasifikasi" /></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Status</td>
                        <td><x-badge-status :status="$surat->status->kode" /></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Dicatat oleh</td>
                        <td>{{ $surat->pembuat->name }}</td>
                    </tr>
                    @if($surat->notes)
                    <tr>
                        <td class="text-muted fw-semibold">Catatan</td>
                        <td>{{ $surat->notes }}</td>
                    </tr>
                    @endif
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

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Riwayat Status</h3>
            </div>
            <div class="card-body">
                <ul class="timeline">
                    @forelse($surat->riwayatStatus as $r)
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

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Disposisi & Follow Up</h3>
            </div>
            <div class="card-body">
                @forelse($surat->disposisi as $d)
                <div class="mb-4 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>{{ $d->dariUser->name ?? '-' }}</strong>
                        <small class="text-muted">{{ $d->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    @if($d->catatan)
                    <p class="mb-1">{{ $d->catatan }}</p>
                    @endif
                    @if($d->kepadaUser)
                    <div class="text-muted small mb-2">
                        Ke: <strong>{{ $d->kepadaUser->name }}</strong>
                        @if($d->kepadaUser->direktorat || $d->kepadaUser->divisi || $d->kepadaUser->departemen)
                        <span class="text-secondary">
                            ({{ $d->kepadaUser->direktorat?->nama ?? '-' }} /
                            {{ $d->kepadaUser->divisi?->nama ?? '-' }} /
                            {{ $d->kepadaUser->departemen?->nama ?? '-' }})
                        </span>
                        @endif
                    </div>
                    @endif
                    @if($d->tanggal_deadline)
                    <div class="text-muted small mb-2">
                        Deadline: <span class="fw-semibold {{ $d->tanggal_deadline->isPast() ? 'text-danger' : '' }}">{{ $d->tanggal_deadline->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @if($d->lampiran->count())
                    <ul class="list-unstyled mt-1 mb-2">
                        @foreach($d->lampiran as $file)
                        <li class="mb-1">
                            <a href="{{ $file->url }}" target="_blank" class="d-flex align-items-center text-break">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/></svg>
                                {{ $file->original_name }}
                                <small class="text-muted ms-2">({{ number_format($file->file_size / 1024, 1) }} KB)</small>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    @if($d->followUps->count())
                    <div class="ms-3 ps-3 border-start border-2 mt-2">
                        @foreach($d->followUps as $fu)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="fw-semibold">{{ $fu->user->name ?? '-' }}</small>
                                <small class="text-muted">{{ $fu->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-1 small">{{ $fu->keterangan }}</p>
                            @if($fu->lampiran)
                            <ul class="list-unstyled mb-0">
                                @foreach($fu->lampiran as $file)
                                <li class="mb-1">
                                    <a href="{{ \Storage::disk('public')->url($file['stored_path']) }}" target="_blank" class="d-flex align-items-center text-break small">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/></svg>
                                        {{ $file['original_name'] }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="collapse" data-bs-target="#followup-form-{{ $d->id }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                        Follow Up
                    </button>
                    <form method="POST" action="{{ route('disposisi.follow-up.store', $d) }}" enctype="multipart/form-data" class="collapse mt-2" id="followup-form-{{ $d->id }}">
                        @csrf
                        <div class="mb-2">
                            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Keterangan follow up..." required></textarea>
                        </div>
                        <div class="mb-2">
                            <input type="file" name="lampiran[]" class="form-control form-control-sm" multiple accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Kirim</button>
                    </form>
                </div>
                @empty
                <p class="text-muted text-center mb-0">Belum ada disposisi.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        @if(in_array(auth()->user()->role->kode, ['sekretaris', 'admin']))
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Aksi</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($surat->status->kode === 'received')
                    <form method="POST" action="{{ route('verifikasi-surat.verify') }}">
                        @csrf
                        <input type="hidden" name="surat_id" value="{{ $surat->id }}">
                        <button type="submit" class="btn btn-info w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
                            Verifikasi
                        </button>
                    </form>
                    @endif

                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modal-disposisi">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2c-8.072 -.49 -14.51 -6.928 -15 -15a2 2 0 0 1 2 -2z"/></svg>
                        Disposisi
                    </button>
                    <a href="{{ route('penerimaan-surat.cetak-disposisi', $surat->uuid) }}" class="btn btn-outline-secondary w-100" target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2"/><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4"/><path d="M7 17l0 1a2 2 0 0 0 2 2h6a2 2 0 0 0 2 -2l0 -1"/></svg>
                        Cetak Lembar Disposisi
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<x-modal-disposisi :surat="$surat" />
@endsection
