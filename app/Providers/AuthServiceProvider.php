<?php

namespace App\Providers;

use App\Models\KomitePersetujuan;
use App\Models\NotulensiRapim;
use App\Models\SuratKeputusan;
use App\Models\SuratMasuk;
use App\Policies\KomitePersetujuanPolicy;
use App\Policies\NotulensiRapimPolicy;
use App\Policies\SuratKeputusanPolicy;
use App\Policies\SuratMasukPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        SuratMasuk::class => SuratMasukPolicy::class,
        SuratKeputusan::class => SuratKeputusanPolicy::class,
        KomitePersetujuan::class => KomitePersetujuanPolicy::class,
        NotulensiRapim::class => NotulensiRapimPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
