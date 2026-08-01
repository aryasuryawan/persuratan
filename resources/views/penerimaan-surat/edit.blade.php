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
                <label class="form-label">Lampiran <small class="text-muted">(opsional — tambah file baru, lampiran lama tetap)</small></label>
                <input type="file" name="lampiran[]" class="form-control @error('lampiran.*') is-invalid @enderror" multiple accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-hint">PDF, JPG, PNG. Maks 10MB per file. Bisa pilih banyak file.</small>
                @error('lampiran.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if($surat->lampiran)
                <ul class="list-unstyled mt-2 mb-0">
                    @foreach($surat->lampiran as $file)
                    <li class="text-secondary small">{{ $file['original_name'] }} ({{ number_format($file['file_size'] / 1024, 1) }} KB)</li>
                    @endforeach
                </ul>
                @endif
            </div>

            <div class="mb-3 form-check form-switch">
                <input type="checkbox" name="is_rahasia" class="form-check-input" id="is_rahasia" value="1" {{ old('is_rahasia', $surat->is_rahasia) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_rahasia">Surat Rahasia</label>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('penerimaan-surat.show', $surat->uuid) }}" class="btn btn-link link-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
