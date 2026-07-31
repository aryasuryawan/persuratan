<?php

namespace App\Actions;

use App\Events\SuratStatusChanged;
use App\Models\SuratMasuk;
use App\Models\User;

class ArsipkanSurat
{
    public function execute(SuratMasuk $surat, User $user, ?string $catatan = null): SuratMasuk
    {
        $statusDariId = $surat->status_id;

        $surat->update(['status_id' => statusId('archived')]);

        event(new SuratStatusChanged(
            surat: $surat,
            statusDariId: $statusDariId,
            statusKeId: statusId('archived'),
            dilakukanOleh: $user,
            catatan: $catatan,
        ));

        return $surat->fresh();
    }
}
