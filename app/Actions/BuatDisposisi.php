<?php

namespace App\Actions;

use App\Events\SuratStatusChanged;
use App\Models\Disposisi;
use App\Models\DisposisiAttachment;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BuatDisposisi
{
    public function execute(
        SuratMasuk $surat,
        User $dariUser,
        string $jenis,
        ?User $atasNamaPimpinan = null,
        ?User $kepadaUser = null,
        ?string $catatan = null,
        ?string $catatanPimpinan = null,
        ?string $tanggalDeadline = null,
        array $lampiran = [],
    ): Disposisi {
        $statusKe = $jenis === 'ke_pelaksana' ? 'assigned' : 'handled_by_leadership';

        $disposisi = Disposisi::create([
            'surat_masuk_id' => $surat->id,
            'dari_user_id' => $dariUser->id,
            'kepada_user_id' => $kepadaUser?->id,
            'jenis' => $jenis,
            'atas_nama_pimpinan_id' => $atasNamaPimpinan?->id,
            'catatan' => $catatan,
            'catatan_pimpinan' => $catatanPimpinan,
            'tanggal_deadline' => $tanggalDeadline,
        ]);

        foreach ($lampiran as $file) {
            if (!$file instanceof UploadedFile || empty($file->getRealPath())) {
                continue;
            }

            try {
                $path = $file->store('disposisi', 'public');
                DisposisiAttachment::create([
                    'disposisi_id' => $disposisi->id,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            } catch (\Throwable $e) {
                continue;
            }
        }

        $oldStatusId = $surat->status_id;
        $surat->update(['status_id' => statusId($statusKe)]);

        event(new SuratStatusChanged(
            surat: $surat,
            statusDariId: $oldStatusId,
            statusKeId: statusId($statusKe),
            dilakukanOleh: $dariUser,
            atasNamaPimpinanId: $atasNamaPimpinan?->id,
            catatan: $catatan ?? $catatanPimpinan,
            disposisiId: $disposisi->id,
        ));

        return $disposisi;
    }
}
