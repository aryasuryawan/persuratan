@php $rekaman = $rekaman ?? null; @endphp

<div class="card">
    <div class="card-header"><h3>Lampiran Dokumen</h3></div>
    <div class="card-body">
        @forelse($rekaman->lampiran as $lampiran)
        <a href="{{ $lampiran->url }}" target="_blank" class="attachment" style="margin-top:0; margin-bottom:8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <div>
                <div class="name">{{ $lampiran->nama_file }}</div>
                <div class="size">{{ strtoupper($lampiran->tipe_file ?? 'FILE') }} · {{ number_format(($lampiran->ukuran_bytes ?? 0) / 1024, 1) }} KB{{ $lampiran->keterangan ? ' · '.$lampiran->keterangan : '' }}</div>
            </div>
        </a>
        @empty
        <p style="color:var(--faint); margin:0;">Tidak ada lampiran.</p>
        @endforelse
    </div>
</div>
