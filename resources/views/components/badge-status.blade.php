@props(['status'])

@php
$map = [
    'received'               => ['color' => 'secondary', 'icon' => 'inbox'],
    'verified'               => ['color' => 'info',      'icon' => 'check'],
    'forwarded_to_leadership'=> ['color' => 'warning',   'icon' => 'arrow-right'],
    'assigned'               => ['color' => 'primary',   'icon' => 'user-check'],
    'handled_by_leadership'  => ['color' => 'purple',    'icon' => 'user-cog'],
    'in_progress'            => ['color' => 'azure',     'icon' => 'loader-2'],
    'completed'              => ['color' => 'success',   'icon' => 'check-circle'],
    'archived'               => ['color' => 'dark',      'icon' => 'archive'],
];
$s = $map[$status] ?? ['color' => 'secondary', 'icon' => 'help'];
$label = ucfirst(str_replace('_', ' ', $status));
@endphp

<span class="badge bg-{{ $s['color'] }}-lt text-{{ $s['color'] }}-lt-fg badge-pill">
    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-xs me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
        @switch($s['icon'])
            @case('inbox') <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"/><path d="M4 9l8 5l8 -5"/><path d="M4 14l8 5l8 -5"/> @break
            @case('check') <path d="M5 12l5 5l10 -10"/> @break
            @case('arrow-right') <path d="M5 12h14"/><path d="M12 5l7 7l-7 7"/> @break
            @case('user-check') <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M15 14l2 2l4 -4"/> @break
            @case('user-cog') <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4"/><path d="M18.42 15.61a4 4 0 0 0 -1.42 -5.61"/><path d="M15 18.59a4 4 0 0 0 5.41 0"/><path d="M15 15.41a4 4 0 0 0 -2.83 -2.83"/><path d="M18.42 20.39a4 4 0 0 0 1.42 -5.61"/><path d="M21.17 15.41a4 4 0 0 0 -2.83 -2.83"/><path d="M21.17 20.39a4 4 0 0 0 -2.83 2.83"/><path d="M15 18.59a4 4 0 0 0 -5.41 0"/> @break
            @case('loader-2') <path d="M12 3v3"/><path d="M12 18v3"/><path d="M4.93 4.93l2.12 2.12"/><path d="M16.95 16.95l2.12 2.12"/><path d="M3 12h3"/><path d="M18 12h3"/><path d="M4.93 19.07l2.12 -2.12"/><path d="M16.95 7.05l2.12 -2.12"/> @break
            @case('check-circle') <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M9 12l2 2l4 -4"/> @break
            @case('archive') <path d="M3 21V7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v14"/><path d="M7 7l5 -3l5 3"/><path d="M7 13l5 3l5 -3"/> @break
            @default <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 9h.01"/><path d="M12 12h.01v3"/> @endswitch
    </svg>
    {{ $label }}
</span>
