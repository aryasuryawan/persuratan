<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme')||'light')</script>
    <title>{{ config('app.name', 'SiMPEL') }} — @yield('title', 'Masuk')</title>
    @if(\App\Models\Setting::get('app_favicon'))
    <link rel="icon" href="{{ \Storage::disk('public')->url(\App\Models\Setting::get('app_favicon')) }}" type="{{ Str::endsWith(\App\Models\Setting::get('app_favicon'), '.ico') ? 'image/x-icon' : 'image/png' }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="border-top-wide border-primary">
<div class="page page-center">
    <div class="container container-tight py-4">
        {{ $slot }}
    </div>
</div>
@stack('scripts')
</body>
</html>
