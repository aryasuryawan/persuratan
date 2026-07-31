@php $rekaman = $rekaman ?? null; @endphp

<div class="card">
    <div class="card-header"><h3>Dokumentasi Kegiatan</h3></div>
    <div class="card-body">
        @if($rekaman->fotoKegiatan->isNotEmpty())
        <div class="row row-cards galeri-kegiatan" style="--tblr-gutter-y:1rem;">
            @foreach($rekaman->fotoKegiatan as $foto)
            <div class="col-6 col-md-3">
                <a href="{{ $foto->url }}" data-glightbox="galeri-{{ $rekaman->getKey() }}" data-description="{{ $foto->caption }}" class="d-block" style="border-radius:8px; overflow:hidden;">
                    <img src="{{ $foto->url }}" class="img-fluid rounded" alt="{{ $foto->caption }}" style="width:100%; aspect-ratio:1/1; object-fit:cover;">
                </a>
                @if($foto->caption)
                <div class="text-muted small mt-1">{{ $foto->caption }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @push('scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox@3.3.0/dist/css/glightbox.min.css">
        <script src="https://cdn.jsdelivr.net/npm/glightbox@3.3.0/dist/js/glightbox.min.js"></script>
        @endpush
        @else
        <p style="color:var(--faint); margin:0;">Belum ada dokumentasi foto.</p>
        @endif
    </div>
</div>
