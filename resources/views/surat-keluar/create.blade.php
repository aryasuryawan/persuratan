@extends('layouts.app')

@section('page-title', 'Tambah Surat Keluar')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('surat-keluar.store') }}" enctype="multipart/form-data">
            @csrf

<div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label required">Nomor Surat</label>
                <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror" value="{{ old('nomor_surat') }}" required>
                @error('nomor_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label required">Klasifikasi</label>
                <select name="klasifikasi_id" class="form-select @error('klasifikasi_id') is-invalid @enderror" required>
                    <option value="">-- Pilih --</option>
                    @foreach($klasifikasi as $k)
                    <option value="{{ $k->id }}" {{ old('klasifikasi_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
                @error('klasifikasi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Kode Surat</label>
                <select name="kode_surat_id" class="form-select @error('kode_surat_id') is-invalid @enderror">
                    <option value="">-- Pilih --</option>
                    @foreach($kodeSurats as $k)
                    <option value="{{ $k->id }}" {{ old('kode_surat_id') == $k->id ? 'selected' : '' }}>[{{ $k->kode }}] {{ $k->nama }}</option>
                    @endforeach
                </select>
                @error('kode_surat_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label required">Tujuan</label>
            <input type="text" name="tujuan_surat" class="form-control @error('tujuan_surat') is-invalid @enderror" value="{{ old('tujuan_surat') }}" required>
            @error('tujuan_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label required">Perihal</label>
            <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror" value="{{ old('perihal') }}" required>
            @error('perihal') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label required">Pimpinan Pengirim</label>
            <select name="pimpinan_pengirim_id" class="form-select @error('pimpinan_pengirim_id') is-invalid @enderror" required>
                <option value="">-- Pilih Pimpinan --</option>
                @foreach($pimpinanList as $p)
                <option value="{{ $p->id }}" {{ old('pimpinan_pengirim_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->name }} @if($p->title ?? $p->jabatan) ({{ $p->title ?? $p->jabatan }}) @endif
                </option>
                @endforeach
            </select>
            <small class="form-hint">Pilih pimpinan yang mengirim surat ini.</small>
            @error('pimpinan_pengirim_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Tanggal Surat</label>
            <input type="date" name="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror" value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
            @error('tanggal_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

            <div class="mb-3">
                <label class="form-label">Terkait Surat Masuk</label>
                <select name="surat_masuk_id" class="form-select @error('surat_masuk_id') is-invalid @enderror">
                    <option value="">-- Tidak terkait --</option>
                    @foreach($suratMasuk as $sm)
                    <option value="{{ $sm->id }}" {{ old('surat_masuk_id') == $sm->id ? 'selected' : '' }}>{{ $sm->nomor_agenda }} - {{ $sm->perihal }}</option>
                    @endforeach
                </select>
                @error('surat_masuk_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Lampiran <small class="text-muted">(opsional)</small></label>
                <input type="file" name="lampiran[]" class="form-control @error('lampiran.*') is-invalid @enderror" multiple accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-hint">PDF, JPG, PNG. Maks 10MB per file. Bisa pilih banyak file.</small>
                @error('lampiran.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('surat-keluar.index') }}" class="btn btn-link link-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
