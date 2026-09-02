@extends('layouts.app')

@section('page-title', 'Tambah Surat Masuk')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('penerimaan-surat.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label required">Pengirim</label>
                <input type="text" name="pengirim" list="daftar-pengirim" class="form-control @error('pengirim') is-invalid @enderror" value="{{ old('pengirim') }}" required>
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
                <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror" value="{{ old('perihal') }}" required>
                @error('perihal') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor Surat</label>
                    <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror" value="{{ old('nomor_surat') }}">
                    @error('nomor_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Klasifikasi</label>
                    <select name="klasifikasi_id" class="form-select @error('klasifikasi_id') is-invalid @enderror" required>
                        <option value="">-- Pilih --</option>
                        @foreach($klasifikasi as $k)
                        <option value="{{ $k->id }}" {{ old('klasifikasi_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    @error('klasifikasi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3" x-data="topikChips({{ $topiks->toJson() }}, {{ json_encode(old('topik_ids', [])) }})">
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
                @error('topik_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label required">Tujuan Pimpinan</label>
                <select name="tujuan_pimpinan_id" class="form-select @error('tujuan_pimpinan_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Pimpinan --</option>
                    @foreach($pimpinanList as $p)
                    <option value="{{ $p->id }}" {{ old('tujuan_pimpinan_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} @if($p->title) ({{ $p->title }}) @endif
                    </option>
                    @endforeach
                </select>
                <small class="form-hint">Pilih pimpinan yang dituju surat ini. Sekretaris akan disposisikan atas nama pimpinan ini.</small>
                @error('tujuan_pimpinan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror" value="{{ old('tanggal_surat') }}">
                    @error('tanggal_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Tanggal Diterima</label>
                    <input type="date" name="tanggal_diterima" class="form-control @error('tanggal_diterima') is-invalid @enderror" value="{{ old('tanggal_diterima', date('Y-m-d')) }}" required>
                    @error('tanggal_diterima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Lampiran <small class="text-muted">(opsional)</small></label>
                <input type="file" name="lampiran[]" class="form-control @error('lampiran.*') is-invalid @enderror" multiple accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-hint">PDF, JPG, PNG. Maks 10MB per file. Bisa pilih banyak file.</small>
                @error('lampiran.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('penerimaan-surat.index') }}" class="btn btn-link link-secondary">Batal</a>
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
