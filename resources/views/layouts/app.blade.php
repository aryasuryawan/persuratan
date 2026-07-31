<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme')||'light')</script>
    <title>{{ \App\Models\Setting::get('app_name', 'SiMPEL') }} — @yield('title', 'Manajemen Surat')</title>
    @if(\App\Models\Setting::get('app_favicon'))
    <link rel="icon" href="{{ \Storage::disk('public')->url(\App\Models\Setting::get('app_favicon')) }}" type="image/png">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div class="page">
    <header class="navbar navbar-expand-md navbar-light d-print-none">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a href="{{ route('dashboard') }}" class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                @if(\App\Models\Setting::get('app_logo'))
                <img src="{{ \Storage::disk('public')->url(\App\Models\Setting::get('app_logo')) }}" alt="{{ \App\Models\Setting::get('app_name', 'SiMPEL') }}" style="height: 32px;">
                @else
                {{ \App\Models\Setting::get('app_name', 'SiMPEL') }}
                @endif
            </a>
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="d-flex flex-column flex-md-row flex-fill align-items-center align-items-md-stretch">
                    <ul class="navbar-nav me-auto">
                        @include('layouts.partials.topbar-navigation')
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a href="#" class="nav-link" id="theme-toggle" title="Toggle theme">
                                <span class="theme-icon-light"><i class="ti ti-moon"></i></span>
                                <span class="theme-icon-dark"><i class="ti ti-sun"></i></span>
                            </a>
                        </li>
                        @include('layouts.partials.topbar-user')
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                @include('layouts.partials.flash-messages')

                @if(View::hasSection('page-header'))
                    <div class="page-header d-print-none mb-5">
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <h2 class="page-title mb-0">@yield('page-title', 'Manajemen Surat')</h2>
                                @if(View::hasSection('page-subtitle'))
                                    <p class="text-muted mb-0">@yield('page-subtitle')</p>
                                @endif
                            </div>
                            <div class="col-auto ms-auto">
                                @yield('page-actions')
                            </div>
                        </div>
                    </div>
                @endif

                @yield('content-head-scripts')
                @yield('content')
            </div>
        </div>

        @include('layouts.partials.footer')
    </div>
</div>
@stack('scripts')
</body>
</html>
