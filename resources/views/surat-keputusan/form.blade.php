@extends('layouts.app')

@section('title', isset($sk) ? 'Edit Surat Keputusan' : 'Catat Surat Keputusan')

@section('content')
@php
    $rekaman = $sk ?? null;
    $isEdit = ! empty($rekaman);
@endphp

@include('arsip.partials._theme')

<div class="simpel-detail">
    <div class="page">

        <div class="breadcrumb"><a href="{{ route('surat-keputusan.index') }}">Surat Keputusan</a> / {{ $isEdit ? 'Edit' : 'Catat Baru' }}</div>
        <div class="page-head">
            <div>
                <h1>{{ $isEdit ? 'Edit Surat Keputusan' : 'Catat Surat Keputusan' }}</h1>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ $isEdit ? route('surat-keputusan.update', $rekaman->uuid) : route('surat-keputusan.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror" value="{{ old('kategori', $rekaman->kategori ?? '') }}" placeholder="mis. Kepegawaian, Organisasi, Program">
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

                    <div class="mb-3">
                        <label class="form-label required">Perihal</label>
                        <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror" value="{{ old('perihal', $rekaman->perihal ?? '') }}" required>
                        @error('perihal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tanggal SK</label>
                            <input type="date" name="tanggal_sk" class="form-control @error('tanggal_sk') is-invalid @enderror" value="{{ old('tanggal_sk', $rekaman?->tanggal_sk?->format('Y-m-d') ?? '') }}" required>
                            @error('tanggal_sk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Berlaku</label>
                            <input type="date" name="tanggal_berlaku" class="form-control @error('tanggal_berlaku') is-invalid @enderror" value="{{ old('tanggal_berlaku', $rekaman?->tanggal_berlaku?->format('Y-m-d') ?? '') }}">
                            @error('tanggal_berlaku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Ditetapkan Oleh</label>
                            <input type="text" name="ditetapkan_oleh" class="form-control @error('ditetapkan_oleh') is-invalid @enderror" value="{{ old('ditetapkan_oleh', $rekaman->ditetapkan_oleh ?? '') }}" placeholder="Nama + jabatan penandatangan">
                            @error('ditetapkan_oleh') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="aktif" {{ old('status', $rekaman->status ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="tidak_berlaku" {{ old('status', $rekaman->status ?? 'aktif') === 'tidak_berlaku' ? 'selected' : '' }}>Tidak Berlaku</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="is_rahasia" value="1" class="form-check-input" {{ old('is_rahasia', $rekaman->is_rahasia ?? false) ? 'checked' : '' }}>
                            <span class="form-check-label">Rahasia — hanya admin, sekretaris, dan yang diberi akses yang dapat melihat</span>
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ringkasan</label>
                        <textarea name="ringkasan" rows="4" class="form-control @error('ringkasan') is-invalid @enderror" placeholder="Ringkasan isi SK (bukan diktum lengkap)">{{ old('ringkasan', $rekaman->ringkasan ?? '') }}</textarea>
                        @error('ringkasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @include('arsip.partials._lampiran-input', ['rekaman' => $rekaman])
                    @include('arsip.partials._foto-input', ['rekaman' => $rekaman])

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}</button>
                        <a href="{{ $isEdit ? route('surat-keputusan.show', $rekaman->uuid) : route('surat-keputusan.index') }}" class="btn">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
