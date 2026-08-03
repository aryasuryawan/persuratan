@extends('layouts.app')

@section('page-title', 'Detail Surat Masuk')

@section('page-header')
@endsection

@section('page-actions')
@can('update', $surat)
<a href="{{ route('penerimaan-surat.edit', $surat->uuid) }}" class="btn btn-outline-primary">
    <i class="ti ti-edit me-2"></i>
    Edit Data Surat
</a>
@endcan
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
                    @can('view', $surat)
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
                    @endcan
                </table>
            </div>
        </div>

        @can('view', $surat)
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
                    @if(in_array(auth()->user()->role->kode, ['sekretaris', 'admin']) || $d->dari_user_id === auth()->id())
                    <button type="button" class="btn btn-sm btn-link btn-pill p-0 float-end" data-bs-toggle="collapse" data-bs-target="#disposisi-edit-{{ $d->id }}" title="Edit disposisi">
                        <i class="ti ti-edit"></i>
                    </button>
                    @endif
                    <form method="POST" action="{{ route('disposisi.update', $d) }}" enctype="multipart/form-data" class="collapse mt-2" id="disposisi-edit-{{ $d->id }}">
                        @csrf @method('PUT')
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
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted">{{ $fu->created_at->format('d/m/Y H:i') }}</small>
                                    @if($fu->user_id === auth()->id() || in_array(auth()->user()->role->kode, ['sekretaris', 'admin']))
                                    <button type="button" class="btn btn-sm btn-link btn-pill p-0" data-bs-toggle="collapse" data-bs-target="#followup-edit-{{ $fu->id }}" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    @endif
                                </div>
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

                            <form method="POST" action="{{ route('disposisi.follow-up.update', [$d, $fu]) }}" enctype="multipart/form-data" class="collapse mt-2" id="followup-edit-{{ $fu->id }}">
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
                        @endforeach
                    </div>
                    @endif

                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="collapse" data-bs-target="#followup-form-{{ $d->id }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                        Follow Up
                    </button>
                    @if(in_array(auth()->user()->role->kode, ['sekretaris', 'admin']) || $d->kepada_user_id === auth()->id())
                    <button type="button" class="btn btn-sm btn-outline-warning mt-2" data-bs-toggle="collapse" data-bs-target="#pindah-form-{{ $d->id }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 6h8"/><path d="M12 4v8"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M9 18l-3 -3"/><path d="M15 18h-6l3 -3z"/></svg>
                        Pindah Disposisi
                    </button>
                    @endif
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
                    <form method="POST" action="{{ route('disposisi.pindah', $d) }}" class="collapse mt-2" id="pindah-form-{{ $d->id }}">
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
                @empty
                <p class="text-muted text-center mb-0">Belum ada disposisi.</p>
                @endforelse
            </div>
        </div>
        @endcan

        @can('view', $surat)
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">History Surat</h3>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="#tab-riwayat-status" class="nav-link active" data-bs-toggle="tab" role="tab">
                            <i class="ti ti-history me-1"></i>Perubahan Status
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#tab-riwayat-data" class="nav-link" data-bs-toggle="tab" role="tab">
                            <i class="ti ti-edit me-1"></i>Perubahan Data
                        </a>
                    </li>
                </ul>
                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="tab-riwayat-status" role="tabpanel">
                        <ul class="timeline">
                            @forelse($surat->riwayatStatus as $r)
                                <x-timeline-item :riwayat="$r" />
                            @empty
                                <li class="timeline-event">
                                    <div class="card timeline-event-card">
                                        <div class="card-body text-muted">Belum ada riwayat status.</div>
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="tab-riwayat-data" role="tabpanel">
                        <ul class="timeline">
                            @forelse($surat->riwayatPerubahanData as $p)
                                <x-timeline-perubahan-data :perubahan="$p" />
                            @empty
                                <li class="timeline-event">
                                    <div class="card timeline-event-card">
                                        <div class="card-body text-muted">Belum ada perubahan data.</div>
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endcan
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

                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modal-disposisi" {{ $surat->disposisi->isNotEmpty() ? 'disabled' : '' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2c-8.072 -.49 -14.51 -6.928 -15 -15a2 2 0 0 1 2 -2z"/></svg>
                        Disposisi
                    </button>
                    <a href="{{ route('penerimaan-surat.cetak-disposisi', $surat->uuid) }}" class="btn btn-outline-secondary w-100" target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2"/><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4"/><path d="M7 17l0 1a2 2 0 0 0 2 2h6a2 2 0 0 0 2 -2l0 -1"/></svg>
                        Cetak Lembar Disposisi
                     </a>
                 </div>
                 @if($surat->klasifikasi->kode === 'restricted' && $canManageAkses)
                 <hr>
                 <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#modal-bagikan-akses">
                     <i class="ti ti-share me-2"></i>Bagikan Akses
                 </button>
                 @endif
             </div>
         </div>
     </div>
     @endif
 </div>

@if(auth()->user()->hasRole('pimpinan') && $canManageAkses)
    <div class="card mt-3">
        <div class="card-header"><h3 class="card-title">Akses Surat</h3></div>
        <div class="card-body">
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modal-bagikan-akses">
                    <i class="ti ti-share me-2"></i>Bagikan Akses
                </button>
            </div>
        </div>
    </div>
@endif

<x-modal-disposisi :surat="$surat" />

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
                        '<div><strong>' + (a.user_name || '') + '</strong> <small class="text-muted">oleh ' + (a.diberikan_oleh_name || '') + ' - ' + (a.created_at_formatted || '') + '</small></div>' +
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
@endpush
@endsection