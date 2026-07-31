@props([
    'label',
    'value',
    'icon',
    'iconColor' => 'primary',
    'trendValue' => null,
    'trendLabel' => null,
    'compareValue' => null,
    'compareLabel' => null,
    'trendUp' => true,
    'actionUrl' => null,
    'actionLabel' => 'Lihat semua',
    'subValue' => null,
    'subLabel' => null,
    'subColor' => 'muted',
    'colClass' => 'col-xl-3 col-sm-6',
])

<div class="{{ $colClass }}">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div>
                    <span class="text-muted text-uppercase fw-semibold">{{ $label }}</span>
                    <h2 class="mb-0 mt-1">{{ $value }}</h2>
                </div>
                <div class="ms-auto">
                    <div class="bg-{{ $iconColor }} bg-opacity-10 p-3 rounded-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" class="ti ti-{{ $icon }} text-{{ $iconColor }}" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            @slot('iconSvg')
                        </svg>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                @if($trendValue !== null && $compareValue !== null)
                    @php
                        $diff = $trendValue - $compareValue;
                        $pct = $compareValue > 0 ? round(($diff / $compareValue) * 100) : 0;
                        $isUp = $diff >= 0;
                    @endphp
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-{{ $isUp ? 'success' : 'danger' }}">
                            {{ $isUp ? '+' : '' }}{{ $pct }}%
                        </span>
                        <span class="text-muted text-sm">{{ $trendLabel ?? 'vs periode lalu' }}</span>
                    </div>
                @elseif($subValue !== null)
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-{{ $subColor }} text-sm fw-semibold">{{ $subValue }}%</span>
                        <span class="text-muted text-sm">{{ $subLabel }}</span>
                    </div>
                @elseif($actionUrl)
                    <a href="{{ $actionUrl }}" class="text-sm text-decoration-none">{{ $actionLabel }} <i class="ti ti-chevron-right ms-1"></i></a>
                @else
                    {{ $slot }}
                @endif
            </div>
        </div>
    </div>
</div>