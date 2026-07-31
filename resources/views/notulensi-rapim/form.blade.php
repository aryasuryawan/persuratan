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

                    <div class="mb-3">
                        <label class="form-label">Daftar Peserta</label>
                        <textarea name="daftar_peserta" rows="3" class="form-control @error('daftar_peserta') is-invalid @enderror" placeholder="Satu nama per baris">{{ old('daftar_peserta', $rekaman->daftar_peserta ?? '') }}</textarea>
                        @error('daftar_peserta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Ringkasan Pembahasan</label>
                        <textarea name="ringkasan_pembahasan" rows="6" class="form-control @error('ringkasan_pembahasan') is-invalid @enderror" required>{{ old('ringkasan_pembahasan', $rekaman->ringkasan_pembahasan ?? '') }}</textarea>
                        @error('ringkasan_pembahasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keputusan / Arahan Pimpinan</label>
                        <textarea name="keputusan_arahan" rows="4" class="form-control @error('keputusan_arahan') is-invalid @enderror">{{ old('keputusan_arahan', $rekaman->keputusan_arahan ?? '') }}</textarea>
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
