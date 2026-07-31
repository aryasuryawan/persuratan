<?php

namespace App\Events;

use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class SuratStatusChanged
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public SuratMasuk $surat,
        public ?int $statusDariId,
        public int $statusKeId,
        public User $dilakukanOleh,
        public ?int $atasNamaPimpinanId = null,
        public ?string $catatan = null,
        public ?int $disposisiId = null,
    ) {}
}
