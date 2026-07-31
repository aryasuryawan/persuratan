@extends('layouts.app')

@section('title', isset($komite) ? 'Edit Hasil Komite Persetujuan' : 'Catat Hasil Komite Persetujuan')

@section('content')
@php
    $rekaman = $komite ?? null;
    $isEdit = ! empty($rekaman);
@endphp

@include('arsip.partials._theme')

<div class="simpel-detail">
    <div class="page">

        <div class="breadcrumb"><a href="{{ route('komite-persetujuan.index') }}">Hasil Komite Persetujuan</a> / {{ $isEdit ? 'Edit' : 'Catat Baru' }}</div>
        <div class="page-head">
            <div>
                <h1>{{ $isEdit ? 'Edit Hasil Komite Persetujuan' : 'Catat Hasil Komite Persetujuan' }}</h1>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ $isEdit ? route('komite-persetujuan.update', $rekaman->uuid) : route('komite-persetujuan.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label required">Judul</label>
                        <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $rekaman->judul ?? '') }}" required placeholder="mis. Persetujuan Pembelian Aset IT Q3">
                        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror" value="{{ old('kategori', $rekaman->kategori ?? '') }}" placeholder="mis. Aset, Proyek, Administrasi">
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

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Jenis</label>
                            <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                                <option value="pembelian" {{ old('jenis', $rekaman->jenis ?? '') === 'pembelian' ? 'selected' : '' }}>Pembelian</option>
                                <option value="prinsip_proyek" {{ old('jenis', $rekaman->jenis ?? '') === 'prinsip_proyek' ? 'selected' : '' }}>Prinsip Proyek</option>
                            </select>
                            @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Tanggal Rapat</label>
                            <input type="date" name="tanggal_rapat" class="form-control @error('tanggal_rapat') is-invalid @enderror" value="{{ old('tanggal_rapat', $rekaman?->tanggal_rapat?->format('Y-m-d') ?? '') }}" required>
                            @error('tanggal_rapat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nilai Pembelian <span class="text-muted">(khusus jenis pembelian)</span></label>
                            <input type="number" step="0.01" min="0" name="nilai_pembelian" class="form-control @error('nilai_pembelian') is-invalid @enderror" value="{{ old('nilai_pembelian', $rekaman->nilai_pembelian ?? '') }}">
                            @error('nilai_pembelian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Daftar Peserta</label>
                        <textarea name="daftar_peserta" rows="3" class="form-control @error('daftar_peserta') is-invalid @enderror" placeholder="Satu nama per baris">{{ old('daftar_peserta', $rekaman->daftar_peserta ?? '') }}</textarea>
                        @error('daftar_peserta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Hasil</label>
                        <select name="hasil" class="form-select @error('hasil') is-invalid @enderror" required>
                            @foreach(['disetujui', 'ditolak', 'revisi', 'ditunda'] as $pilihan)
                            <option value="{{ $pilihan }}" {{ old('hasil', $rekaman->hasil ?? '') === $pilihan ? 'selected' : '' }} class="text-capitalize">{{ $pilihan }}</option>
                            @endforeach
                        </select>
                        @error('hasil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="is_rahasia" value="1" class="form-check-input" {{ old('is_rahasia', $rekaman->is_rahasia ?? false) ? 'checked' : '' }}>
                            <span class="form-check-label">Rahasia — hanya admin, sekretaris, dan yang diberi akses yang dapat melihat</span>
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Keputusan</label>
                        <textarea name="catatan_keputusan" rows="4" class="form-control @error('catatan_keputusan') is-invalid @enderror" placeholder="Catatan / pertimbangan hasil keputusan">{{ old('catatan_keputusan', $rekaman->catatan_keputusan ?? '') }}</textarea>
                        @error('catatan_keputusan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @include('arsip.partials._lampiran-input', ['rekaman' => $rekaman])
                    @include('arsip.partials._foto-input', ['rekaman' => $rekaman])

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}</button>
                        <a href="{{ $isEdit ? route('komite-persetujuan.show', $rekaman->uuid) : route('komite-persetujuan.index') }}" class="btn">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
