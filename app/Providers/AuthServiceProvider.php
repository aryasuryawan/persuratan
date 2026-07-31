<?php

namespace App\Providers;

use App\Models\SuratMasuk;
use App\Policies\SuratMasukPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        SuratMasuk::class => SuratMasukPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
