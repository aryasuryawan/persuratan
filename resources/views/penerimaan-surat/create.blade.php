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
                <input type="text" name="pengirim" class="form-control @error('pengirim') is-invalid @enderror" value="{{ old('pengirim') }}" required>
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
