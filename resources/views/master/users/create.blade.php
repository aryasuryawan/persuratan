@extends('layouts.app')

@section('page-title', 'Tambah User')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.users.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Nama</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Direktorat</label>
                    <select name="direktorat_id" class="form-select @error('direktorat_id') is-invalid @enderror" data-control="select" data-placeholder="Pilih Direktorat..." required>
                        <option value="">-- Pilih --</option>
                        @foreach($direktorats as $id => $nama)
                        <option value="{{ $id }}" {{ old('direktorat_id') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                    @error('direktorat_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Divisi</label>
                    <select name="divisi_id" class="form-select @error('divisi_id') is-invalid @enderror" data-control="select" data-placeholder="Pilih Divisi...">
                        <option value="">-- Pilih --</option>
                        @foreach($divisis as $d)
                        <option value="{{ $d->id }}" {{ old('divisi_id') == $d->id ? 'selected' : '' }}>[{{ $d->direktorat->kode }}] {{ $d->nama }}</option>
                        @endforeach
                    </select>
                    @error('divisi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Departemen</label>
                    <select name="departemen_id" class="form-select @error('departemen_id') is-invalid @enderror" data-control="select" data-placeholder="Pilih Departemen...">
                        <option value="">-- Pilih --</option>
                        @foreach($departemens as $d)
                        <option value="{{ $d->id }}" {{ old('departemen_id') == $d->id ? 'selected' : '' }}>[{{ $d->divisi->kode }}] {{ $d->nama }}</option>
                        @endforeach
                    </select>
                    @error('departemen_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Role</label>
                    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                        <option value="">-- Pilih --</option>
                        @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>{{ $r->label }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mb-3">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span class="form-check-label">Aktif</span>
                </label>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('master.users.index') }}" class="btn btn-link link-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
