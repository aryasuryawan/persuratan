@props(['klasifikasi'])

@php
$iconMap = [
    'Rahasia' => 'lock',
    'Terlalu Rahasia' => 'lock-off',
    'Biasa' => 'file-text',
    'Penting' => 'alert-circle',
    'Segera' => 'clock',
];
$icon = $iconMap[$klasifikasi->nama] ?? 'tag';
$color = $klasifikasi->color ?? 'primary';
@endphp

<span class="badge bg-{{ $color }} text-{{ $color }}-fg badge-pill">
    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-xs me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
        @switch($icon)
            @case('lock') <path d="M10 11V7a4 4 0 0 1 8 0v4"/><path d="M14 11v8a2 2 0 0 1 -2 2H8a2 2 0 0 1 -2 -2v-8"/> @break
            @case('lock-off') <path d="M8 11V7a4 4 0 0 1 8 0v1m0 4v7a2 2 0 0 1 -2 2H8a2 2 0 0 1 -2 -2v-7"/><path d="M3 3l18 18"/> @break
            @case('file-text') <path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21H7a2 2 0 0 1 -2 -2V5a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M9 9h6"/><path d="M9 13h6"/><path d="M9 17h4"/> @break
            @case('alert-circle') <path d="M12 9v2m0 4v.01"/><path d="M5 19a10 10 0 1 1 14 0c0 2.5 -1.5 4.5 -3 5.5l-.5.5H8.5l-.5 -.5a10 10 0 0 1 -3 -5.5"/> @break
            @case('clock') <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 7v5l3 3"/> @break
            @default <path d="M5 5m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 5m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M7 5h10"/><path d="M7 17h10"/><path d="M5 7v10"/><path d="M17 7v10"/> @endswitch
    </svg>
    {{ $klasifikasi->nama }}
</span>
