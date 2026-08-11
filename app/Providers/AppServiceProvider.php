<?php

namespace App\Providers;

use App\Models\Setting;
use App\View\Composers\TopbarNavigationComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.partials.topbar-navigation', TopbarNavigationComposer::class);

        Paginator::defaultView('vendor.pagination.tabler');
        Paginator::defaultSimpleView('vendor.pagination.simple-tabler');

        RateLimiter::for('ai-ask', function (Request $request) {
            $perMinute = (int) Setting::get('ai_rate_limit', '10');
            $perMinute = max(1, min($perMinute, 1000));

            return Limit::perMinute($perMinute)->by($request->user()?->id ?: $request->ip());
        });
    }
}
