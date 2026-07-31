@extends('layouts.app')

@section('page-title', 'Tambah Divisi')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.divisis.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label required">Direktorat</label>
                <select name="direktorat_id" class="form-select @error('direktorat_id') is-invalid @enderror" required>
                    <option value="">Pilih Direktorat...</option>
                    @foreach($direktorats as $id => $nama)
                    <option value="{{ $id }}" {{ old('direktorat_id') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
                @error('direktorat_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label required">Kode</label>
                <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode') }}" required>
                @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label required">Nama</label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Aktif</label>
                <select name="aktif" class="form-select">
                    <option value="1" {{ old('aktif', '1') == '1' ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ old('aktif') === '0' ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('master.divisis.index') }}" class="btn btn-link link-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
