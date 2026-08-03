<?php

namespace App\Console\Commands;

use App\Models\FotoKegiatan;
use App\Models\Lampiran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ArsipPindahFilePrivate extends Command
{
    protected $signature = 'arsip:pindah-file-private';

    protected $description = 'Pindahkan semua media arsip dari disk public ke disk private (local)';

    public function handle(): int
    {
        $dipindah = 0;
        $gagal = 0;

        foreach (Lampiran::all() as $lampiran) {
            if (Storage::disk('local')->exists($lampiran->path)) {
                continue;
            }
            if (Storage::disk('public')->exists($lampiran->path)) {
                Storage::disk('local')->put($lampiran->path, Storage::disk('public')->get($lampiran->path));
                Storage::disk('public')->delete($lampiran->path);
                $dipindah++;
            } else {
                $gagal++;
                $this->warn("File lampiran #{$lampiran->id} tidak ditemukan di disk mana pun: {$lampiran->path}");
            }
        }

        foreach (FotoKegiatan::all() as $foto) {
            if (Storage::disk('local')->exists($foto->path)) {
                continue;
            }
            if (Storage::disk('public')->exists($foto->path)) {
                Storage::disk('local')->put($foto->path, Storage::disk('public')->get($foto->path));
                Storage::disk('public')->delete($foto->path);
                $dipindah++;
            } else {
                $gagal++;
                $this->warn("File foto #{$foto->id} tidak ditemukan di disk mana pun: {$foto->path}");
            }
        }

        $this->info("Selesai. File dipindah: {$dipindah}, gagal/tidak ditemukan: {$gagal}.");

        return self::SUCCESS;
    }
}
