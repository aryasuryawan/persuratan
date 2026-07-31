<?php

namespace App\Services;

use App\Models\StatusSurat;
use App\Models\SuratMasuk;

class WorkflowService
{
    public function canTransitionTo(SuratMasuk $surat, string $targetStatusKode): bool
    {
        $current = $surat->status->kode;
        $urutanSaatIni = $surat->status->urutan;
        $target = StatusSurat::where('kode', $targetStatusKode)->first();

        if (!$target) {
            return false;
        }

        if ($current === 'archived') {
            return false;
        }

        if ($targetStatusKode === 'forwarded_to_leadership' && $current !== 'verified') {
            return false;
        }

        if ($targetStatusKode === 'assigned' && !in_array($current, ['forwarded_to_leadership', 'handled_by_leadership', 'verified'])) {
            return false;
        }

        return $target->urutan >= $urutanSaatIni;
    }
}
