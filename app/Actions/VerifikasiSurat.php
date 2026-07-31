<?php

namespace App\Actions;

use App\Events\SuratStatusChanged;
use App\Models\SuratMasuk;
use App\Models\User;

class VerifikasiSurat
{
    public function execute(SuratMasuk $surat, User $user, ?string $catatan = null): SuratMasuk
    {
        $surat->update([
            'status_id' => statusId('verified'),
            'verified_by' => $user->id,
            'verified_at' => now(),
        ]);

        event(new SuratStatusChanged(
            surat: $surat,
            statusDariId: statusId('received'),
            statusKeId: statusId('verified'),
            dilakukanOleh: $user,
            catatan: $catatan,
        ));

        return $surat->fresh();
    }
}
