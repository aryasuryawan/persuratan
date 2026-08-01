@extends('layouts.app')

@section('title', 'Detail Surat Masuk')

@section('content')
@php
    $disposisiCount = $surat->disposisi->count();
    $followUpCount = $surat->disposisi->sum(fn($d) => $d->followUps->count());
    $revisiCount = $surat->riwayatPerubahanData->filter(fn($p) => $p->disposisi_id !== null)->count();
    $lastDisposisi = $surat->disposisi->sortByDesc('created_at')->first();
    $bisaSekretarisAdmin = in_array(auth()->user()->role->kode, ['sekretaris', 'admin']);
    $bisaBagikanAkses = $surat->klasifikasi->kode === 'restricted' && $canManageAkses;
    $isRestricted = $surat->klasifikasi->kode === 'restricted';
    $statusBadgeMap = [
        'received' => 'badge-muted',
        'verified' => 'badge-warning',
        'forwarded_to_leadership' => 'badge-warning',
        'assigned' => 'badge-warning',
        'handled_by_leadership' => 'badge-warning',
        'in_progress' => 'badge-warning',
        'completed' => 'badge-success',
        'archived' => 'badge-dark',
    ];
    $statusBadge = $statusBadgeMap[$surat->status->kode] ?? 'badge-muted';
    $initial = fn($name) => $name ? strtoupper(substr($name, 0, 1)) : '?';
@endphp

@include('arsip.partials._theme')

<div class="simpel-detail">
    <div class="page">

        <div class="breadcrumb"><a href="{{ route('penerimaan-surat.index') }}">Surat Masuk</a> / Detail</div>
        <div class="page-head">
            <div>
                <h1>{{ $surat->perihal }}</h1>
                <div class="badge-row">
                    <span class="badge {{ $isRestricted ? 'badge-dark' : 'badge-muted' }}">
                        @if($isRestricted)
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        @endif
                        {{ $surat->klasifikasi->nama }}
                    </span>
                    <span class="badge {{ $statusBadge }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>{{ $surat->status->label }}</span>
                    <span class="badge badge-muted">{{ $surat->nomor_agenda }}</span>
                </div>
            </div>
            @can('update', $surat)
            <a href="{{ route('penerimaan-surat.edit', $surat->uuid) }}" class="btn btn-outline-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                Edit Data Surat
            </a>
            @endcan
        </div>

        <div class="grid">
            <!-- ===================== MAIN COLUMN ===================== -->
            <div>

                <!-- Detail surat -->
                <div class="card">
                    <div class="card-header"><h3>Informasi Surat</h3></div>
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">No. Surat</div>
                                <div class="datagrid-value">{{ $surat->nomor_surat ?? '-' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Pengirim</div>
                                <div class="datagrid-value">{{ $surat->pengirim }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Tanggal Surat</div>
                                <div class="datagrid-value">{{ $surat->tanggal_surat ? $surat->tanggal_surat->format('d/m/Y') : '-' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Tanggal Diterima</div>
                                <div class="datagrid-value">{{ $surat->tanggal_diterima->format('d/m/Y') }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Dicatat oleh</div>
                                <div class="datagrid-value">{{ $surat->pembuat->name }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Klasifikasi</div>
                                <div class="datagrid-value"><span class="badge {{ $isRestricted ? 'badge-dark' : 'badge-muted' }}">@if($isRestricted)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>@endif{{ $surat->klasifikasi->nama }}</span></div>
                            </div>
                            @if($surat->tujuanPimpinan)
                            <div class="datagrid-item">
                                <div class="datagrid-title">Tujuan Pimpinan</div>
                                <div class="datagrid-value">{{ $surat->tujuanPimpinan->name }} @if($surat->tujuanPimpinan->title ?? $surat->tujuanPimpinan->jabatan) ({{ $surat->tujuanPimpinan->title ?? $surat->tujuanPimpinan->jabatan }}) @endif</div>
                            </div>
                            @endif
                            @if($surat->notes)
                            <div class="datagrid-item span-2">
                                <div class="datagrid-title">Catatan</div>
                                <div class="datagrid-value">{{ $surat->notes }}</div>
                            </div>
                            @endif
                            @can('view', $surat)
                            <div class="datagrid-item span-2">
                                <div class="datagrid-title">Lampiran</div>
                                <div class="datagrid-value">
                                    @if($surat->lampiran)
                                        @foreach($surat->lampiran as $file)
                                        <a href="{{ \Storage::disk('public')->url($file['stored_path']) }}" target="_blank" class="attachment">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            <div>
                                                <div class="name">{{ $file['original_name'] }}</div>
                                                <div class="size">{{ number_format($file['file_size'] / 1024, 1) }} KB · {{ strtoupper(pathinfo($file['original_name'], PATHINFO_EXTENSION)) }}</div>
                                            </div>
                                        </a>
                                        @endforeach
                                    @else
                                        <em style="color:var(--faint);">Tidak ada lampiran</em>
                                    @endif
                                </div>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>

                <!-- Disposisi -->
                @can('view', $surat)
                <div class="card">
                    <div class="card-header">
                        <h3>Disposisi &amp; Follow Up</h3>
                        <span class="muted-count">{{ $disposisiCount }} disposisi @if($followUpCount) · {{ $followUpCount }} follow-up @endif</span>
                    </div>
                    <div class="card-body">

                        @forelse($surat->disposisi as $d)
                        @php
                            $dRevisi = $surat->riwayatPerubahanData->filter(fn($p) => $p->disposisi_id === $d->id)->count();
                        @endphp
                        <div class="disp-item">
                            <div class="disp-main">
                                <div class="disp-top">
                                    <div class="disp-who">
                                        <div class="avatar">{{ $initial($d->dariUser?->name) }}</div>
                                        <div>
                                            <div class="disp-name">
                                                {{ $d->dariUser->name ?? '-' }}
                                                @if($dRevisi > 0)
                                                <span class="badge badge-warning disp-revisi">Revisi {{ $dRevisi }}</span>
                                                @endif
                                            </div>
                                            <div class="disp-time">{{ $d->created_at->format('d/m/Y H:i') }}</div>
                                        </div>
                                    </div>
                                    @if($bisaSekretarisAdmin || $d->dari_user_id === auth()->id())
                                    <div class="d-flex align-items-center gap-1">
                                        <svg class="icon-edit" data-bs-toggle="collapse" data-bs-target="#disposisi-edit-{{ $d->id }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="cursor:pointer;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                        <form method="POST" action="{{ route('disposisi.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Yakin hapus disposisi ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-link link-danger p-0" style="line-height:1;">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                                <div class="collapse" id="disposisi-edit-{{ $d->id }}">
                                    <form method="POST" action="{{ route('disposisi.update', $d) }}" enctype="multipart/form-data" class="mt-2">
                                        @csrf @method('PUT')
                                        @if($errors->any())
                                        <div class="alert alert-danger alert-sm py-2 mb-2">
                                            @foreach($errors->all() as $err)
                                            <div>{{ $err }}</div>
                                            @endforeach
                                        </div>
                                        @endif
                                        <div class="mb-2">
                                            <label class="form-label small">Instruksi</label>
                                            <div class="form-checklist">
                                                @foreach(['Diperiksa', 'Diperhatikan', 'Ditindaklanjuti', 'Dilaksanakan', 'Arsip/Ditolak'] as $instruksi)
                                                <label class="form-checklist-item">
                                                    <input type="checkbox" name="instruksi[]" value="{{ $instruksi }}" 
                                                        {{ in_array($instruksi, $d->instruksi ?? []) ? 'checked' : '' }} 
                                                        class="form-checklist-input">
                                                    <span class="form-checklist-label">{{ $instruksi }}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">Disposisi kepada</label>
                                            @php
                                                $disposisiKepada = $d->disposisi_kepada ?? [];
                                                $userNames = \App\Models\User::where('is_active', true)->pluck('name')->toArray();
                                                $selectedUsers = array_intersect($disposisiKepada, $userNames);
                                                $freeTexts = array_values(array_diff($disposisiKepada, $userNames));
                                                $parseFree = function ($v) {
                                                    if (preg_match('/^(.+)\s*<([^>]+)>$/', $v, $m)) {
                                                        return ['name' => trim($m[1]), 'email' => trim($m[2])];
                                                    }
                                                    return ['name' => $v, 'email' => ''];
                                                };
                                            @endphp
                                            <select name="disposisi_kepada[]" class="form-select form-select-sm" multiple>
                                                @foreach(\App\Models\User::where('is_active', true)->get() as $u)
                                                <option value="{{ $u->name }}" {{ in_array($u->name, $selectedUsers) ? 'selected' : '' }}>
                                                    {{ $u->name }} @if($u->jabatan)({{ $u->jabatan }})@endif
                                                </option>
                                                @endforeach
                                            </select>
                                            <small class="form-hint">Tahan Ctrl/Cmd untuk pilih multiple user.</small>
                                            <div class="mt-1">
                                                <label class="form-label small">Tambah nama/jabatan bebas <span class="text-muted">(email wajib agar notifikasi terkirim)</span></label>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="disposisi_kepada_free[]" class="form-control" placeholder="Nama">
                                                    <input type="email" name="disposisi_kepada_free_email[]" class="form-control" placeholder="Email (wajib)" style="max-width:220px;">
                                                    <button type="button" class="btn btn-outline-secondary" id="btn-add-disposisi-free-edit-{{ $d->id }}">Tambah</button>
                                                </div>
                                                <div id="disposisi-free-list-edit-{{ $d->id }}" class="mt-1">
                                                    @foreach($freeTexts as $text)
                                                    @php $parsed = $parseFree($text); @endphp
                                                    <div class="input-group input-group-sm mt-1">
                                                        <input type="text" name="disposisi_kepada_free[]" class="form-control" value="{{ $parsed['name'] }}">
                                                        <input type="email" name="disposisi_kepada_free_email[]" class="form-control" value="{{ $parsed['email'] }}" placeholder="Email (wajib)" style="max-width:220px;">
                                                        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()">Hapus</button>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">Isi Disposisi</label>
                                            <textarea name="catatan" class="form-control form-control-sm" rows="3">{{ old('catatan', $d->catatan) }}</textarea>
                                        </div>
                                        @if(auth()->user()->role->kode === 'sekretaris')
                                        <div class="mb-2">
                                            <label class="form-label small">Catatan Pimpinan</label>
                                            <textarea name="catatan_pimpinan" class="form-control form-control-sm" rows="2">{{ old('catatan_pimpinan', $d->catatan_pimpinan) }}</textarea>
                                        </div>
                                        @endif
                                        <div class="mb-2">
                                            <label class="form-label small">Batas Waktu</label>
                                            <input type="date" name="tanggal_deadline" class="form-control form-control-sm" value="{{ old('tanggal_deadline', $d->tanggal_deadline?->format('Y-m-d')) }}">
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                    </form>
                                </div>
                                @if($d->catatan)
                                <div class="disp-msg">{{ $d->catatan }}</div>
                                @endif
                                <div class="disp-meta">
                                    @if($d->kepadaUser)
                                    <span>
                                        Ke: <b>{{ $d->kepadaUser->name }}</b>
                                        @if($d->kepadaUser->direktorat || $d->kepadaUser->divisi || $d->kepadaUser->departemen)
                                        <span style="color:var(--muted);">
                                            ({{ $d->kepadaUser->direktorat?->nama ?? '-' }} /
                                            {{ $d->kepadaUser->divisi?->nama ?? '-' }} /
                                            {{ $d->kepadaUser->departemen?->nama ?? '-' }})
                                        </span>
                                        @endif
                                    </span>
                                    @endif
                                    @if($d->disposisi_kepada)
                                    <span style="margin-left:8px;">
                                        Disposisi kepada: <b>{{ implode(', ', $d->disposisi_kepada) }}</b>
                                    </span>
                                    @endif
                                    @if($d->instruksi)
                                    <span style="margin-left:8px;">
                                        Instruksi: <b>{{ implode(', ', $d->instruksi) }}</b>
                                    </span>
                                    @endif
                                    @if($d->tanggal_deadline)
                                    <span class="chip-deadline {{ $d->tanggal_deadline->isPast() ? 'chip-warning' : 'chip-muted' }}" style="{{ $d->tanggal_deadline->isPast() ? 'background:var(--warning-light); color:var(--warning);' : 'background:var(--tblr-secondary-bg); color:var(--muted);' }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:11px;height:11px;"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>
                                        Deadline {{ $d->tanggal_deadline->format('d/m/Y') }}
                                    </span>
                                    @endif
                                </div>

                                @if($d->lampiran->count())
                                <div class="mb-2">
                                    @foreach($d->lampiran as $file)
                                    <a href="{{ $file->url }}" target="_blank" class="attachment" style="padding:6px 10px; margin-top:0; margin-bottom:6px;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <div><div class="name">{{ $file->original_name }}</div></div>
                                    </a>
                                    @endforeach
                                </div>
                                @endif

                                @if($d->followUps->count())
                                @foreach($d->followUps as $fu)
                                <div class="followup">
                                    <div class="followup-top">
                                        <div class="followup-who"><div class="avatar" style="width:22px;height:22px;font-size:10px;">{{ $initial($fu->user?->name) }}</div>{{ $fu->user->name ?? '-' }}</div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="followup-time">{{ $fu->created_at->format('d/m/Y H:i') }}</span>
                                            @if($fu->user_id === auth()->id() || $bisaSekretarisAdmin)
                                            <svg class="icon-edit" data-bs-toggle="collapse" data-bs-target="#followup-edit-{{ $fu->id }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;cursor:pointer;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="followup-text">{{ $fu->keterangan }}</div>
                                    @if($fu->lampiran)
                                    <div class="mb-1">
                                        @foreach($fu->lampiran as $file)
                                        <a href="{{ \Storage::disk('public')->url($file['stored_path']) }}" target="_blank" class="attachment" style="background:var(--card); padding:6px 10px; margin-top:0; margin-bottom:6px;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            <div><div class="name">{{ $file['original_name'] }}</div></div>
                                        </a>
                                        @endforeach
                                    </div>
                                    @endif
                                    <div class="collapse" id="followup-edit-{{ $fu->id }}">
                                        <form method="POST" action="{{ route('disposisi.follow-up.update', [$d, $fu]) }}" enctype="multipart/form-data">
                                            @csrf @method('PUT')
                                            <div class="mb-2">
                                                <textarea name="keterangan" class="form-control form-control-sm" rows="2" required>{{ old('keterangan', $fu->keterangan) }}</textarea>
                                            </div>
                                            @if($fu->lampiran)
                                            <div class="mb-2">
                                                <small class="text-muted d-block mb-1">Hapus lampiran (centang untuk hapus):</small>
                                                @foreach($fu->lampiran as $file)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="hapus_lampiran[]" value="{{ $file['stored_path'] }}" id="hapus-fu-{{ $fu->id }}-{{ $loop->index }}">
                                                    <label class="form-check-label" for="hapus-fu-{{ $fu->id }}-{{ $loop->index }}">{{ $file['original_name'] }}</label>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                            <div class="mb-2">
                                                <input type="file" name="lampiran[]" class="form-control form-control-sm" multiple accept=".pdf,.jpg,.jpeg,.png">
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                                @endif

                                <div class="disp-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#followup-form-{{ $d->id }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        Follow Up
                                    </button>
                                    @if($bisaSekretarisAdmin || $d->kepada_user_id === auth()->id())
                                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#pindah-form-{{ $d->id }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h8"/><path d="M12 4v8"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M9 18l-3 -3"/><path d="M15 18h-6l3 -3z"/></svg>
                                        Pindah Disposisi
                                    </button>
                                    @endif
                                    @if($d->completed_at)
                                    <span class="badge badge-success" style="padding:6px 12px; align-self:center;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                        Disposisi ini sudah selesai
                                    </span>
                                    @endif
                                </div>

                                <div class="collapse" id="followup-form-{{ $d->id }}">
                                    <form method="POST" action="{{ route('disposisi.follow-up.store', $d) }}" enctype="multipart/form-data">
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
                                <div class="collapse" id="pindah-form-{{ $d->id }}">
                                    <form method="POST" action="{{ route('disposisi.pindah', $d) }}">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="form-label small required">Pindah Ke</label>
                                            <select name="pelaksana_id" class="form-select form-select-sm" required>
                                                <option value="">-- Pilih Pelaksana --</option>
                                                @foreach(\App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'unit_lain'))->where('is_active', true)->get() as $u)
                                                <option value="{{ $u->id }}" {{ $u->id === $d->kepada_user_id ? 'disabled' : '' }}>{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">Catatan</label>
                                            <textarea name="catatan" class="form-control form-control-sm" rows="2"></textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">Deadline</label>
                                            <input type="date" name="tanggal_deadline" class="form-control form-control-sm">
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-warning">Pindahkan</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p style="color:var(--muted); text-align:center; margin:0;">Belum ada disposisi.</p>
                        @endforelse

                    </div>
                </div>
                @endcan

                <!-- History (collapsed by default) -->
                @can('view', $surat)
                <div class="card">
                    <button type="button" class="history-toggle" onclick="document.getElementById('histbody').classList.toggle('open'); this.querySelector('svg').classList.toggle('rot')">
                        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; vertical-align:-2px; margin-right:6px;"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/></svg>History Surat — {{ $surat->riwayatStatus->count() }} catatan status</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="history-body" id="histbody">
                        <div class="tabs">
                            <a class="tab active" href="#hist-status" data-tab="hist-status" onclick="event.preventDefault(); switchHistTab('hist-status');">Status <span class="count">{{ $surat->riwayatStatus->count() }}</span></a>
                            <a class="tab" href="#hist-data" data-tab="hist-data" onclick="event.preventDefault(); switchHistTab('hist-data');">Perubahan Data <span class="count">{{ $surat->riwayatPerubahanData->count() }}</span></a>
                            <a class="tab" href="#hist-email" data-tab="hist-email" onclick="event.preventDefault(); switchHistTab('hist-email');">Notifikasi Email <span class="count">{{ $emailLogs->count() ?? 0 }}</span></a>
                        </div>
                        <div style="height:8px;"></div>

                        <div class="tl" id="hist-status">
                            @forelse($surat->riwayatStatus as $r)
                            @php
                                $done = in_array($r->statusKe?->kode, ['completed', 'archived']);
                            @endphp
                            <div class="tl-item">
                                <div class="tl-dot {{ $done ? 'done' : '' }}">
                                    @if($done)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>@endif
                                </div>
                                <div class="tl-card" @if($done) style="border-color:var(--success);" @endif>
                                    <div class="tl-head"><span class="tl-title">{{ $r->statusKe?->label ?? 'Status' }}</span><span class="tl-when">{{ $r->created_at->diffForHumans() }}</span></div>
                                    <div class="tl-body">
                                        Oleh <b>{{ $r->dilakukanOleh->name ?? '-' }}</b>
                                        @if($r->atasNamaPimpinan && $r->atasNamaPimpinan->id !== $r->dilakukan_oleh_id)
                                        <span style="color:var(--faint);"> — atas nama </span><b>{{ $r->atasNamaPimpinan->name }}</b>
                                        @endif
                                        @if($r->disposisi?->kepadaUser)
                                        &rarr; Ke <b>{{ $r->disposisi->kepadaUser->name }}</b>
                                        @if($r->disposisi->kepadaUser->direktorat || $r->disposisi->kepadaUser->divisi || $r->disposisi->kepadaUser->departemen)
                                        <span style="color:var(--faint);">({{ $r->disposisi->kepadaUser->direktorat?->nama ?? '-' }} / {{ $r->disposisi->kepadaUser->divisi?->nama ?? '-' }} / {{ $r->disposisi->kepadaUser->departemen?->nama ?? '-' }})</span>
                                        @endif
                                        @endif
                                        @if($r->disposisi?->disposisi_kepada)
                                        <div style="font-size:12px; color:var(--muted); margin-top:4px;">
                                            Disposisi kepada: {{ implode(', ', $r->disposisi->disposisi_kepada) }}
                                        </div>
                                        @endif
                                        @if($r->disposisi?->tanggal_deadline)
                                        . Deadline <b>{{ $r->disposisi->tanggal_deadline->format('d/m/Y') }}</b>
                                        @endif
                                        @if($r->catatan)
                                        <span style="color:var(--muted);"> "{{ $r->catatan }}"</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p style="color:var(--muted); margin:0;">Belum ada riwayat status.</p>
                            @endforelse
                        </div>

                        <div class="tl" id="hist-data" style="display:none;">
                            @forelse($surat->riwayatPerubahanData as $p)
                            <div class="tl-item">
                                <div class="tl-dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:9px;height:9px;color:var(--warning);"><path d="M4 20h4l10.5 -10.5a2.83 2.83 0 0 0 -4 -4l-10.5 10.5z"/><path d="M13.5 6.5l4 4"/></svg></div>
                                <div class="tl-card">
                                    <div class="tl-head"><span class="tl-title">@if($p->disposisi_id) Perubahan Data Disposisi @else Perubahan Data Surat @endif</span><span class="tl-when">{{ $p->created_at->diffForHumans() }}</span></div>
                                    <div class="tl-body">
                                        Oleh <b>{{ $p->dilakukanOleh->name ?? '-' }}</b>
                                        @if($p->disposisi?->kepadaUser)
                                        &rarr; Disposisi #{{ $p->disposisi->id }} ke <b>{{ $p->disposisi->kepadaUser->name }}</b>
                                        @endif
                                        @if($p->disposisi?->disposisi_kepada)
                                        <div style="font-size:12px; color:var(--muted); margin-top:4px;">
                                            Disposisi kepada: {{ implode(', ', $p->disposisi->disposisi_kepada) }}
                                        </div>
                                        @endif
                                        @if($p->perubahan)
                                        <ul style="margin:6px 0 0; padding-left:16px;">
                                            @foreach($p->perubahan as $item)
                                            <li style="font-size:12px;">
                                                <b>{{ $item['label'] ?? '' }}</b>:
                                                <span style="color:var(--danger); text-decoration:line-through;">{{ $item['lama'] ?? '' }}</span>
                                                &rarr;
                                                <b style="color:var(--success);">{{ $item['baru'] ?? '' }}</b>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p style="color:var(--muted); margin:0;">Belum ada perubahan data.</p>
                            @endforelse
                        </div>

                        <div class="tl" id="hist-email" style="display:none;">
                            @forelse($emailLogs as $log)
                            @php
                                $ok = $log->status === 'sent';
                            @endphp
                            <div class="tl-item">
                                <div class="tl-dot {{ $ok ? 'done' : '' }}">
                                    @if($ok)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    @else<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:9px;height:9px;color:var(--danger);"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    @endif
                                </div>
                                <div class="tl-card" @if(!$ok) style="border-color:var(--danger);" @endif>
                                    <div class="tl-head">
                                        <span class="tl-title">{{ $log->recipient_name }} &lt;{{ $log->recipient_email }}&gt;</span>
                                        <span class="tl-when">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="tl-body">
                                        @if($ok)
                                        <span class="badge" style="background:var(--success-light, #e6f7ef); color:var(--success);">Terkirim</span>
                                        @else
                                        <span class="badge" style="background:var(--danger-light, #fdecec); color:var(--danger);">Gagal</span>
                                        @endif
                                        <span style="color:var(--muted);">Subjek: "{{ $log->subject }}"</span>
                                        @if($log->mailer_used)
                                        <span style="color:var(--faint);"> · via {{ $log->mailer_used }}</span>
                                        @endif
                                        @if(!$ok && $log->error_message)
                                        <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $log->error_message }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p style="color:var(--muted); margin:0;">Belum ada notifikasi email.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <script>
                    function switchHistTab(id) {
                        document.querySelectorAll('.history-body .tab').forEach(function(t) {
                            t.classList.toggle('active', t.getAttribute('data-tab') === id);
                        });
                        document.getElementById('hist-status').style.display = (id === 'hist-status') ? '' : 'none';
                        document.getElementById('hist-data').style.display = (id === 'hist-data') ? '' : 'none';
                        document.getElementById('hist-email').style.display = (id === 'hist-email') ? '' : 'none';
                    }
                </script>
                @endcan

            </div>

            <!-- ===================== SIDEBAR ===================== -->
            <div class="side">

                <div class="card">
                    <div class="card-header"><h3>Ringkasan</h3></div>
                    <div class="card-body" style="padding:8px 20px;">
                        <div class="summary-row"><span class="k">Status</span><span class="v"><span class="badge {{ $statusBadge }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>{{ $surat->status->label }}</span></span></div>
                        <div class="summary-row"><span class="k">Pemegang saat ini</span><span class="v">{{ $lastDisposisi?->kepadaUser?->name ?? '-' }}</span></div>
                        <div class="summary-row"><span class="k">Deadline terakhir</span><span class="v">{{ $lastDisposisi?->tanggal_deadline?->format('d/m/Y') ?? '-' }}</span></div>
                        <div class="summary-row"><span class="k">Total revisi</span><span class="v">{{ $revisiCount }}×</span></div>
                    </div>
                </div>

                @if($bisaSekretarisAdmin || $bisaBagikanAkses)
                <div class="card">
                    <div class="card-header"><h3>Aksi</h3></div>
                    <div class="card-body">
                        @if($bisaSekretarisAdmin)
                            @if($surat->status->kode === 'received')
                            <form method="POST" action="{{ route('verifikasi-surat.verify') }}" style="margin-bottom:10px;">
                                @csrf
                                <input type="hidden" name="surat_id" value="{{ $surat->id }}">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                    Verifikasi
                                </button>
                            </form>
                            @endif
                            <button type="button" class="btn btn-primary btn-block" style="margin-bottom:10px;" data-bs-toggle="modal" data-bs-target="#modal-disposisi" {{ $surat->disposisi->isNotEmpty() ? 'disabled' : '' }}>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                                Disposisi
                            </button>
                            <a href="{{ route('penerimaan-surat.cetak-disposisi', $surat->uuid) }}" class="btn btn-block" style="margin-bottom:10px; text-decoration:none;" target="_blank">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                Cetak Lembar Disposisi
                            </a>
                        @endif

                        @if($bisaBagikanAkses)
                            @if($bisaSekretarisAdmin)
                            <div class="divider"></div>
                            @endif
                            <button type="button" class="btn btn-outline-primary btn-block" data-bs-toggle="modal" data-bs-target="#modal-bagikan-akses">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.6" y1="13.5" x2="15.4" y2="17.5"/><line x1="15.4" y1="6.5" x2="8.6" y2="10.5"/></svg>
                                Bagikan Akses
                            </button>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<x-modal-disposisi :surat="$surat" />

@if($bisaBagikanAkses)
<div class="modal fade" id="modal-bagikan-akses" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bagikan Akses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Cari User</label>
                    <input type="text" class="form-control" id="user-search" placeholder="Ketik nama atau email...">
                    <div class="dropdown-menu mt-1" id="user-search-results" style="display:block;position:relative;"></div>
                </div>
                <button type="button" class="btn btn-primary mb-3" id="btn-add-akses">Tambahkan</button>
                <div id="akses-list"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@if($bisaBagikanAkses)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modal-bagikan-akses');
    if (!modal) return;

    const suratUuid = '{{ $surat->uuid }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const searchInput = modal.querySelector('#user-search');
    const searchResults = modal.querySelector('#user-search-results');
    const addBtn = modal.querySelector('#btn-add-akses');
    const aksesList = modal.querySelector('#akses-list');

    let selectedUserId = null;
    let selectedUserName = '';

    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            const q = this.value.trim();
            if (q.length < 2) { searchResults.innerHTML = ''; return; }
            fetch('/api/user-search?q=' + encodeURIComponent(q))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    searchResults.innerHTML = data.map(function(u) {
                        return '<a class="dropdown-item d-flex justify-content-between align-items-center" href="#" data-id="' + u.id + '" data-name="' + u.name + '">' +
                            '<span>' + u.name + ' (' + u.email + ')</span>' +
                            '<span class="badge text-bg-secondary">' + (u.role_label || '') + '</span></a>';
                    }).join('');
                    searchResults.querySelectorAll('[data-id]').forEach(function(el) {
                        el.addEventListener('click', function(e) {
                            e.preventDefault();
                            selectedUserId = this.getAttribute('data-id');
                            selectedUserName = this.getAttribute('data-name');
                            searchInput.value = selectedUserName;
                            searchResults.innerHTML = '';
                        });
                    });
                });
        }, 300));
    }

    if (addBtn) {
        addBtn.addEventListener('click', function() {
            if (!selectedUserId) return;
            fetch('/api/surat-masuk/' + suratUuid + '/akses', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ user_id: selectedUserId })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.message) loadAksesList();
                selectedUserId = null;
                searchInput.value = '';
            });
        });
    }

    function loadAksesList() {
        if (!aksesList) return;
        fetch('/api/surat-masuk/' + suratUuid + '/akses')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                aksesList.innerHTML = data.map(function(a) {
                    return '<div class="d-flex justify-content-between align-items-center py-2 border-bottom">' +
                        '<div><strong>' + (a.user_name || '') + '</strong> <small class="text-secondary">oleh ' + (a.diberikan_oleh_name || '') + ' - ' + (a.created_at_formatted || '') + '</small></div>' +
                        '<button type="button" class="btn btn-sm btn-danger btn-hapus-akses" data-user-id="' + a.user_id + '">Hapus</button>' +
                        '</div>';
                }).join('');
                aksesList.querySelectorAll('.btn-hapus-akses').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var userId = this.getAttribute('data-user-id');
                        if (!confirm('Yakin ingin mencabut akses user ini?')) return;
                        fetch('/api/surat-masuk/' + suratUuid + '/akses/' + userId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            }
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.message) loadAksesList();
                        });
                    });
                });
            });
    }

    function debounce(fn, delay) {
        var timer = null;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function() { fn.apply(context, args); }, delay);
        };
    }

    loadAksesList();
});
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[id^="btn-add-disposisi-free-edit-"]').forEach(function(btn) {
                const dispId = btn.id.replace('btn-add-disposisi-free-edit-', '');
                const list = document.getElementById('disposisi-free-list-edit-' + dispId);
                if (!list) return;
                btn.addEventListener('click', function() {
                    const div = document.createElement('div');
                    div.className = 'input-group input-group-sm mt-1';
                    div.innerHTML = '<input type="text" name="disposisi_kepada_free[]" class="form-control" placeholder="Nama">' +
                        '<input type="email" name="disposisi_kepada_free_email[]" class="form-control" placeholder="Email (wajib)" style="max-width:220px;">' +
                        '<button type="button" class="btn btn-outline-danger" onclick="this.closest(\'.input-group\').remove()">Hapus</button>';
                    list.appendChild(div);
                });
            });
        });

        // Auto-open collapse from URL fragment after update
        if (window.location.hash) {
            const target = document.querySelector(window.location.hash);
            if (target && target.classList.contains('collapse')) {
                new bootstrap.Collapse(target, { show: true });
            }
        }
    });
</script>
@endcan
