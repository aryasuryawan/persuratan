@extends('layouts.app')

@section('page-title', 'Edit Klasifikasi')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.klasifikasi.update', $klasifikasi) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label required">Kode</label>
                <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode', $klasifikasi->kode) }}" required>
                @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label required">Nama</label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $klasifikasi->nama) }}" required>
                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label required">Warna</label>
                <select name="color" class="form-select @error('color') is-invalid @enderror" required>
                    @foreach(['secondary','info','warning','primary','success','danger','dark','azure','purple'] as $c)
                    <option value="{{ $c }}" {{ old('color', $klasifikasi->color) == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
                @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $klasifikasi->deskripsi) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-check">
                    <input type="checkbox" name="is_rahasia" value="1" class="form-check-input" {{ old('is_rahasia', $klasifikasi->is_rahasia) ? 'checked' : '' }}>
                    <span class="form-check-label">Rahasia</span>
                </label>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('master.klasifikasi.index') }}" class="btn btn-link link-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
