@extends('layouts.app')

@section('title', isset($rapim) ? 'Edit Notulensi Rapat Pimpinan' : 'Catat Notulensi Rapat Pimpinan')

@section('content')
@php
    $rekaman = $rapim ?? null;
    $isEdit = ! empty($rekaman);
@endphp

@include('arsip.partials._theme')

<div class="simpel-detail">
    <div class="page">

        <div class="breadcrumb"><a href="{{ route('notulensi-rapim.index') }}">Notulensi Rapat Pimpinan</a> / {{ $isEdit ? 'Edit' : 'Catat Baru' }}</div>
        <div class="page-head">
            <div>
                <h1>{{ $isEdit ? 'Edit Notulensi Rapat Pimpinan' : 'Catat Notulensi Rapat Pimpinan' }}</h1>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ $isEdit ? route('notulensi-rapim.update', $rekaman->uuid) : route('notulensi-rapim.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label required">Judul Rapat</label>
                            <input type="text" name="judul_rapat" class="form-control @error('judul_rapat') is-invalid @enderror" value="{{ old('judul_rapat', $rekaman->judul_rapat ?? '') }}" required>
                            @error('judul_rapat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', $rekaman?->tanggal?->format('Y-m-d') ?? '') }}" required>
                            @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror" value="{{ old('kategori', $rekaman->kategori ?? '') }}" placeholder="mis. Kepegawaian, Program, Anggaran">
                            @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Klasifikasi</label>
                            <select name="klasifikasi_id" class="form-select @error('klasifikasi_id') is-invalid @enderror">
                                <option value="">— Pilih Klasifikasi —</option>
                                @foreach(\App\Models\Klasifikasi::orderBy('nama')->get() as $klas)
                                    <option value="{{ $klas->id }}" {{ old('klasifikasi_id', $rekaman->klasifikasi_id ?? '') == $klas->id ? 'selected' : '' }}>{{ $klas->nama }}</option>
                                @endforeach
                            </select>
                            @error('klasifikasi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3" x-data="topikChips({{ ($topiks ?? collect())->toJson() }}, {{ json_encode(old('topik_ids', $selectedTopiks ?? [])) }})">
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
                                @foreach(($topiks ?? collect()) as $t)
                                <option value="{{ $t->id }}">{{ $t->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="topik_ids" :value="JSON.stringify(topikIds)">
                        <small class="form-hint">Opsional. Pilih satu atau lebih topik.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Daftar Peserta</label>
                        <textarea name="daftar_peserta" rows="3" class="form-control @error('daftar_peserta') is-invalid @enderror" placeholder="Satu nama per baris">{{ old('daftar_peserta', $rekaman->daftar_peserta ?? '') }}</textarea>
                        @error('daftar_peserta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Ringkasan Pembahasan</label>
                        <textarea name="ringkasan_pembahasan" id="ringkasan_pembahasan" class="form-control wysiwyg @error('ringkasan_pembahasan') is-invalid @enderror" required rows="6">{{ old('ringkasan_pembahasan', $rekaman->ringkasan_pembahasan ?? '') }}</textarea>
                        @error('ringkasan_pembahasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keputusan / Arahan Pimpinan</label>
                        <textarea name="keputusan_arahan" id="keputusan_arahan" class="form-control wysiwyg @error('keputusan_arahan') is-invalid @enderror" rows="4">{{ old('keputusan_arahan', $rekaman->keputusan_arahan ?? '') }}</textarea>
                        @error('keputusan_arahan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @include('arsip.partials._lampiran-input', ['rekaman' => $rekaman])
                    @include('arsip.partials._foto-input', ['rekaman' => $rekaman])

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}</button>
                        <a href="{{ $isEdit ? route('notulensi-rapim.show', $rekaman->uuid) : route('notulensi-rapim.index') }}" class="btn">Batal</a>
                    </div>
                </form>
            </div>
        </div>

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
