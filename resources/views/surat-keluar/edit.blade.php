@extends('layouts.app')

@section('page-title', 'Edit Surat Keluar')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('surat-keluar.update', $surat->uuid) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Nomor Surat</label>
                    <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror" value="{{ old('nomor_surat', $surat->nomor_surat) }}" required>
                    @error('nomor_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label required">Klasifikasi</label>
                    <select name="klasifikasi_id" class="form-select @error('klasifikasi_id') is-invalid @enderror" required>
                        <option value="">-- Pilih --</option>
                        @foreach($klasifikasi as $k)
                        <option value="{{ $k->id }}" {{ old('klasifikasi_id', $surat->klasifikasi_id) == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    @error('klasifikasi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kode Surat</label>
                    <select name="kode_surat_id" class="form-select @error('kode_surat_id') is-invalid @enderror">
                        <option value="">-- Pilih --</option>
                        @foreach($kodeSurats as $k)
                        <option value="{{ $k->id }}" {{ old('kode_surat_id', $surat->kode_surat_id) == $k->id ? 'selected' : '' }}>[{{ $k->kode }}] {{ $k->nama }}</option>
                        @endforeach
                    </select>
                    @error('kode_surat_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                <label class="form-label required">Tujuan</label>
                <input type="text" name="tujuan_surat" class="form-control @error('tujuan_surat') is-invalid @enderror" value="{{ old('tujuan_surat', $surat->tujuan_surat) }}" required>
                @error('tujuan_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label required">Perihal</label>
                <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror" value="{{ old('perihal', $surat->perihal) }}" required>
                @error('perihal') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label required">Pimpinan Pengirim</label>
                <select name="pimpinan_pengirim_id" class="form-select @error('pimpinan_pengirim_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Pimpinan --</option>
                    @foreach($pimpinanList as $p)
                    <option value="{{ $p->id }}" {{ old('pimpinan_pengirim_id', $surat->pimpinan_pengirim_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} @if($p->title ?? $p->jabatan) ({{ $p->title ?? $p->jabatan }}) @endif
                    </option>
                    @endforeach
                </select>
                <small class="form-hint">Pilih pimpinan yang mengirim surat ini.</small>
                @error('pimpinan_pengirim_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal Surat</label>
                <input type="date" name="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror" value="{{ old('tanggal_surat', $surat->tanggal_surat->format('Y-m-d')) }}" required>
                @error('tanggal_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Terkait Surat Masuk</label>
                <select name="surat_masuk_id" class="form-select @error('surat_masuk_id') is-invalid @enderror">
                    <option value="">-- Tidak terkait --</option>
                    @foreach($suratMasuk as $sm)
                    <option value="{{ $sm->id }}" {{ old('surat_masuk_id', $surat->surat_masuk_id) == $sm->id ? 'selected' : '' }}>{{ $sm->nomor_agenda }} - {{ $sm->perihal }}</option>
                    @endforeach
                </select>
                @error('surat_masuk_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Lampiran Saat Ini</label>
                @if($surat->lampiran)
                <ul class="list-unstyled mb-2" id="existing-lampiran">
                    @foreach($surat->lampiran as $i => $file)
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
                <a href="{{ route('surat-keluar.show', $surat->uuid) }}" class="btn btn-link link-secondary">Batal</a>
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
