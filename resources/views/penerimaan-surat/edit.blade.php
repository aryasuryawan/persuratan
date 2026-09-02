@extends('layouts.app')

@section('page-title', 'Edit Surat Masuk')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('penerimaan-surat.update', $surat->uuid) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nomor Agenda</label>
                <input type="text" class="form-control" value="{{ $surat->nomor_agenda }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label required">Pengirim</label>
                <input type="text" name="pengirim" list="daftar-pengirim" class="form-control @error('pengirim') is-invalid @enderror" value="{{ old('pengirim', $surat->pengirim) }}" required>
                <datalist id="daftar-pengirim">
                    @foreach($organisasiList as $o)
                    <option value="{{ $o->nama }}"></option>
                    @endforeach
                </datalist>
                <small class="form-hint">Pilih dari daftar, atau ketik nama baru untuk otomatis menambah master data.</small>
                @error('pengirim') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label required">Perihal</label>
                <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror" value="{{ old('perihal', $surat->perihal) }}" required>
                @error('perihal') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor Surat</label>
                    <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror" value="{{ old('nomor_surat', $surat->nomor_surat) }}">
                    @error('nomor_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Klasifikasi</label>
                    <select name="klasifikasi_id" class="form-select @error('klasifikasi_id') is-invalid @enderror" required>
                        <option value="">-- Pilih --</option>
                        @foreach($klasifikasi as $k)
                        <option value="{{ $k->id }}" {{ old('klasifikasi_id', $surat->klasifikasi_id) == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    @error('klasifikasi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3" x-data="topikChips({{ $topiks->toJson() }}, {{ json_encode(old('topik_ids', $selectedTopiks)) }})">
                <label class="form-label">Topik / Subjek</label>
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <template x-for="selected in selectedTopiks" :key="selected.id">
                        <span class="badge bg-primary-lt d-flex align-items-center gap-1">
                            <span x-text="selected.nama"></span>
                            <button type="button" class="btn-close btn-close-sm" @click="removeTopik(selected.id)" style="font-size: 0.5rem;"></button>
                        </span>
                    </template>
                </div>
                <div class="input-group">
                    <select class="form-select form-select-sm" x-ref="topikSelect" @change="addTopik($event.target.value); $event.target.value = ''">
                        <option value="">-- Pilih Topik --</option>
                        @foreach($topiks as $t)
                        <option value="{{ $t->id }}">{{ $t->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="topik_ids" :value="JSON.stringify(topikIds)">
                <small class="form-hint">Opsional. Pilih satu atau lebih topik.</small>
            </div>

            <div class="mb-3">
                <label class="form-label required">Tujuan Pimpinan</label>
                <select name="tujuan_pimpinan_id" class="form-select @error('tujuan_pimpinan_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Pimpinan --</option>
                    @foreach($pimpinanList as $p)
                    <option value="{{ $p->id }}" {{ old('tujuan_pimpinan_id', $surat->tujuan_pimpinan_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} @if($p->title) ({{ $p->title }}) @endif
                    </option>
                    @endforeach
                </select>
                <small class="form-hint">Pilih pimpinan yang dituju surat ini.</small>
                @error('tujuan_pimpinan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror" value="{{ old('tanggal_surat', $surat->tanggal_surat?->format('Y-m-d')) }}">
                    @error('tanggal_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Tanggal Diterima</label>
                    <input type="date" name="tanggal_diterima" class="form-control @error('tanggal_diterima') is-invalid @enderror" value="{{ old('tanggal_diterima', $surat->tanggal_diterima->format('Y-m-d')) }}" required>
                    @error('tanggal_diterima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $surat->notes) }}</textarea>
                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Lampiran Saat Ini</label>
                @if($surat->lampiran)
                <ul class="list-unstyled mb-2" id="existing-lampiran">
                    @foreach($surat->lampiran as $file)
                    <li class="mb-1 d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/></svg>
                        <span>{{ $file['original_name'] }}</span>
                        <small class="text-muted ms-2">({{ number_format($file['file_size'] / 1024, 1) }} KB)</small>
                        <label class="form-check ms-3 mb-0">
                            <input type="checkbox" name="hapus_lampiran[]" value="{{ $file['stored_path'] }}" class="form-check-input">
                            <span class="form-check-label text-danger small">Hapus</span>
                        </label>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted mb-2"><em>Tidak ada lampiran</em></p>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label">Tambah Lampiran Baru <small class="text-muted">(opsional)</small></label>
                <input type="file" name="lampiran[]" class="form-control @error('lampiran.*') is-invalid @enderror" multiple accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-hint">PDF, JPG, PNG. Maks 10MB per file.</small>
                @error('lampiran.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('penerimaan-surat.show', $surat->uuid) }}" class="btn btn-link link-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function topikChips(topiks, initial = []) {
    return {
        topiks: topiks,
        selectedTopiks: topiks.filter(t => initial.includes(t.id)),
        topikIds: initial,
        addTopik(id) {
            id = parseInt(id);
            if (!id || this.topikIds.includes(id)) return;
            const topik = this.topiks.find(t => t.id === id);
            if (topik) {
                this.selectedTopiks.push(topik);
                this.topikIds.push(id);
            }
        },
        removeTopik(id) {
            this.selectedTopiks = this.selectedTopiks.filter(t => t.id !== id);
            this.topikIds = this.topikIds.filter(i => i !== id);
        }
    }
}
</script>
@endpush
