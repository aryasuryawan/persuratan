<?php

namespace App\Providers;

use App\Events\SuratStatusChanged;
use App\Listeners\CatatRiwayatStatus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(
            SuratStatusChanged::class,
            CatatRiwayatStatus::class,
        );
    }
}
