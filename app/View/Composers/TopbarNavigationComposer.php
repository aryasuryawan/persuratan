<?php

namespace App\View\Composers;

use App\Models\SuratMasuk;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TopbarNavigationComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();
        $role = $user?->role->kode;

        $pending = 0;
        if (in_array($role, ['sekretaris', 'admin'])) {
            $pending = Cache::remember('topbar.pending_surat', 60, fn () => SuratMasuk::where('status_id', statusId('received'))->count());
        }

        $view->with([
            'topbarRole' => $role,
            'pendingSurat' => (int) $pending,
        ]);
    }
}
