@extends('layouts.app')

@section('page-title', 'Tambah Relasi Sekretaris-Pimpinan')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.sekretaris-pimpinan.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label required">Sekretaris</label>
                <select name="sekretaris_id" class="form-select @error('sekretaris_id') is-invalid @enderror" required>
                    <option value="">-- Pilih --</option>
                    @foreach($sekretaris as $s)
                    <option value="{{ $s->id }}" {{ old('sekretaris_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('sekretaris_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label required">Pimpinan</label>
                <select name="pimpinan_ids[]" class="form-select @error('pimpinan_ids') is-invalid @enderror" data-control="select" multiple required>
                    @foreach($pimpinan as $p)
                    <option value="{{ $p->id }}" {{ in_array($p->id, old('pimpinan_ids', [])) ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                @error('pimpinan_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @error('pimpinan_ids.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-hint">Pilih satu atau lebih pimpinan untuk sekretaris ini.</div>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('master.sekretaris-pimpinan.index') }}" class="btn btn-link link-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
