@props(['riwayat'])

@php
    $statusKode = $riwayat->statusKe->kode;
    $iconMap = [
        'received' => ['icon' => 'inbox', 'color' => 'secondary'],
        'verified' => ['icon' => 'circle-check', 'color' => 'success'],
        'disposed' => ['icon' => 'send', 'color' => 'info'],
        'completed' => ['icon' => 'circle-check', 'color' => 'green'],
    ];
    $iconData = $iconMap[$statusKode] ?? ['icon' => 'circle-dot', 'color' => 'secondary'];
@endphp

<li class="timeline-event">
    <div class="timeline-event-icon bg-{{ $iconData['color'] }}-lt">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            @if($iconData['icon'] === 'inbox')
                <path d="M22 12h-6l-2 3h-4l-2 -3h-6" /><path d="M5.45 5.11l-1.41 5.35a2 2 0 0 0 1.47 2.44l.92 .18a2 2 0 0 0 1.87 -1.11l1.21 -2.36a1 1 0 0 1 .87 -.54l.45 -.01a2 2 0 0 1 2 2v1a2 2 0 0 0 2 2h.5" /><path d="M18 18h1a2 2 0 0 0 2 -v-1a2 2 0 0 0 -2 -2h-1" />
            @elseif($iconData['icon'] === 'circle-check')
                <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" />
            @elseif($iconData['icon'] === 'send')
                <path d="M10 14l-2 -2m0 0l5 -5m-5 5l4 4m1 -4l-1.5 -1.5" /><path d="M22 2l-2.5 5.5" /><path d="M3.64 3.64l1.77 5.58a2 2 0 0 0 1.44 1.26l6.63.54a2 2 0 0 0 1.9 -1.15l2.36 -5.15a1 1 0 0 0-.78 -1.43l-8.1 -1.36a1 1 0 0 0-1.17.6L3.64 3.64z" />
            @else
                <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
            @endif
        </svg>
    </div>
    <div class="card timeline-event-card">
        <div class="card-body">
            <div class="text-secondary float-end">{{ $riwayat->created_at->diffForHumans() }}</div>
            <h4>{{ $riwayat->statusKe->label }}</h4>
            <p class="text-secondary mb-1">
                Oleh: <strong>{{ $riwayat->dilakukanOleh->name }}</strong>
                <span class="text-secondary">({{ $riwayat->dilakukanOleh->role->label ?? 'User' }})</span>
                @if($riwayat->atasNamaPimpinan && $riwayat->atasNamaPimpinan->id !== $riwayat->dilakukan_oleh_id)
                    <span class="text-secondary"> — atas nama </span>
                    <strong>{{ $riwayat->atasNamaPimpinan->name }}</strong>
                    <span class="text-secondary">({{ $riwayat->atasNamaPimpinan->role->label ?? 'User' }})</span>
                @endif
            </p>
            @if($riwayat->disposisi?->kepadaUser)
            <p class="text-secondary small mb-1">
                Ke: <strong>{{ $riwayat->disposisi->kepadaUser->name }}</strong>
                @php $target = $riwayat->disposisi->kepadaUser; @endphp
                @if($target->direktorat || $target->divisi || $target->departemen)
                <span class="text-secondary">
                    ({{ $target->direktorat?->nama ?? '-' }} /
                    {{ $target->divisi?->nama ?? '-' }} /
                    {{ $target->departemen?->nama ?? '-' }})
                </span>
                @endif
            </p>
            @endif
            @if($riwayat->disposisi?->tanggal_deadline)
            <p class="text-muted small mb-1">
                Deadline: <span class="fw-semibold {{ $riwayat->disposisi->tanggal_deadline->isPast() ? 'text-danger' : '' }}">{{ $riwayat->disposisi->tanggal_deadline->format('d/m/Y') }}</span>
            </p>
            @endif
            @if($riwayat->catatan)
                <p class="mb-0">{{ $riwayat->catatan }}</p>
            @endif
        </div>
    </div>
</li>
