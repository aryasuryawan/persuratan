@php $rekaman = $rekaman ?? null; @endphp

<div class="card">
    <div class="card-header"><h3>Foto Kegiatan</h3></div>
    <div class="card-body">
        @if($rekaman && $rekaman->relationLoaded('fotoKegiatan') && $rekaman->fotoKegiatan->isNotEmpty())
        <div class="mb-3">
            <label class="form-label">Foto Saat Ini <span class="text-muted">(centang untuk hapus)</span></label>
            <div class="row row-cards">
                @foreach($rekaman->fotoKegiatan as $foto)
                <div class="col-6 col-md-3 mb-3">
                    <div class="position-relative">
                        <img src="{{ $foto->url }}" class="img-fluid rounded" alt="{{ $foto->caption }}">
                        <label class="form-check position-absolute top-0 start-0 m-1 bg-white rounded p-1 mb-0">
                            <input class="form-check-input" type="checkbox" name="hapus_foto[]" value="{{ $foto->id }}">
                            <span class="form-check-label small">Hapus</span>
                        </label>
                    </div>
                    @if($foto->caption)
                    <div class="text-muted small mt-1">{{ $foto->caption }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <input type="file" name="foto_kegiatan[]" class="form-control" multiple accept="image/*">
        <small class="form-hint">Bisa pilih beberapa foto sekaligus. JPG, PNG, WEBP. Maks 5MB per file.</small>
        @error('foto_kegiatan.*') <div class="text-danger">{{ $message }}</div> @enderror

        <label class="form-label mt-3">Caption Foto <span class="text-muted">(opsional, berlaku untuk semua foto baru)</span></label>
        <input type="text" name="foto_caption" class="form-control" value="{{ old('foto_caption') }}" placeholder="mis. Rapat Komite Persetujuan Juli 2026">
        @error('foto_caption') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
</div>
