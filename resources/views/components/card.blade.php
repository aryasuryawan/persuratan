@props([
    'title' => null,
    'subtitle' => null,
    'headerActions' => null,
    'footer' => null,
    'class' => '',
    'bodyClass' => '',
    'headerClass' => '',
])

<div class="card {{ $class }}">
    @if($title || $headerActions || isset($headerSlot))
    <div class="card-header {{ $headerClass }} d-flex align-items-center justify-content-between flex-wrap gap-2">
        @if($title)
            <div>
                <h3 class="card-title mb-0">{{ $title }}</h3>
                @if($subtitle)
                    <div class="text-muted text-sm">{{ $subtitle }}</div>
                @endif
            </div>
        @endif
        @if(isset($headerSlot))
            <div class="d-flex flex-wrap gap-2">
                {{ $headerSlot }}
            </div>
        @elseif($headerActions)
            <div class="ms-auto">
                {{ $headerActions }}
            </div>
        @endif
    </div>
    @endif

    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>

    @isset($footer)
    <div class="card-footer">
        {{ $footer }}
    </div>
    @endisset
</div>