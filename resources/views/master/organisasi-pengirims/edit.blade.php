@extends('layouts.app')

@section('page-title', 'Edit Organisasi Pengirim')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.organisasi-pengirims.update', $organisasiPengirim) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label required">Nama</label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $organisasiPengirim->nama) }}" required>
                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <input type="text" name="alamat" class="form-control @error('alamat') is-invalid @enderror" value="{{ old('alamat', $organisasiPengirim->alamat) }}">
                @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Kontak</label>
                <input type="text" name="kontak" class="form-control @error('kontak') is-invalid @enderror" value="{{ old('kontak', $organisasiPengirim->kontak) }}">
                @error('kontak') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Aktif</label>
                <select name="aktif" class="form-select">
                    <option value="1" {{ old('aktif', $organisasiPengirim->aktif) ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ !old('aktif', $organisasiPengirim->aktif) ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Perbarui</button>
                <a href="{{ route('master.organisasi-pengirims.index') }}" class="btn btn-link link-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
