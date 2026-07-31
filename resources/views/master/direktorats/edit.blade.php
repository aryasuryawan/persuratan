@extends('layouts.app')

@section('page-title', 'Edit Direktorat')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.direktorats.update', $direktorat) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label required">Kode</label>
                <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode', $direktorat->kode) }}" required>
                @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label required">Nama</label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $direktorat->nama) }}" required>
                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Aktif</label>
                <select name="aktif" class="form-select">
                    <option value="1" {{ old('aktif', $direktorat->aktif) ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ !old('aktif', $direktorat->aktif) ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Perbarui</button>
                <a href="{{ route('master.direktorats.index') }}" class="btn btn-link link-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
