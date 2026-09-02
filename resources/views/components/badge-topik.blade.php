@props(['topiks' => []])

@if(count($topiks) > 0)
    <div class="d-flex flex-wrap gap-1">
        @foreach($topiks as $t)
            <span class="badge bg-{{ $t->color }}-lt text-{{ $t->color }}">{{ $t->nama }}</span>
        @endforeach
    </div>
@endif
