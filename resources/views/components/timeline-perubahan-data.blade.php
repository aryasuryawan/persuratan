@props(['perubahan'])

<li class="timeline-event">
    <div class="timeline-event-icon bg-warning-lt">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M4 20h4l10.5 -10.5a2.83 2.83 0 0 0 -4 -4l-10.5 10.5z" />
            <path d="M13.5 6.5l4 4" />
        </svg>
    </div>
    <div class="card timeline-event-card">
        <div class="card-body">
            <div class="text-secondary float-end">{{ $perubahan->created_at->diffForHumans() }}</div>
            <h4>
                @if($perubahan->disposisi_id)
                    Perubahan Data Disposisi
                @else
                    Perubahan Data Surat
                @endif
            </h4>
            <p class="text-secondary mb-2">
                Oleh: <strong>{{ $perubahan->dilakukanOleh->name ?? '-' }}</strong>
                <span class="text-secondary">({{ $perubahan->dilakukanOleh->role->label ?? 'User' }})</span>
                &mdash; {{ $perubahan->created_at->format('d/m/Y H:i') }}
            </p>
            @if($perubahan->disposisi)
            <p class="text-muted small mb-2">
                Disposisi #{{ $perubahan->disposisi->id }}
                @if($perubahan->disposisi->kepadaUser)
                &rarr; <strong>{{ $perubahan->disposisi->kepadaUser->name }}</strong>
                @endif
            </p>
            @endif
            <ul class="list-unstyled mb-1">
                @foreach($perubahan->perubahan ?? [] as $item)
                <li class="mb-1 small">
                    <strong>{{ $item['label'] }}</strong>:
                    <span class="text-danger text-decoration-line-through">{{ $item['lama'] }}</span>
                    &rarr;
                    <span class="text-success fw-semibold">{{ $item['baru'] }}</span>
                </li>
                @endforeach
            </ul>
            @if($perubahan->catatan)
            <p class="text-muted small mb-0">{{ $perubahan->catatan }}</p>
            @endif
        </div>
    </div>
</li>
