@extends('layouts.app')

@section('page-title', 'Edit Role')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.roles.update', $role) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label required">Kode</label>
                <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode', $role->kode) }}" required>
                @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label required">Label</label>
                <input type="text" name="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $role->label) }}" required>
                @error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('master.roles.index') }}" class="btn btn-link link-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
