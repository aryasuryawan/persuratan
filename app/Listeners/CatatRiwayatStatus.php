<?php

namespace App\Listeners;

use App\Events\SuratStatusChanged;
use App\Models\RiwayatStatus;

class CatatRiwayatStatus
{
    public function handle(SuratStatusChanged $event): void
    {
        RiwayatStatus::create([
            'surat_masuk_id' => $event->surat->id,
            'status_dari_id' => $event->statusDariId,
            'status_ke_id' => $event->statusKeId,
            'dilakukan_oleh_id' => $event->dilakukanOleh?->id,
            'atas_nama_pimpinan_id' => $event->atasNamaPimpinanId,
            'catatan' => $event->catatan,
            'disposisi_id' => $event->disposisiId,
        ]);
    }
}
