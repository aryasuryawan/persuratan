<?php

namespace App\Http\Controllers;

use App\Events\SuratStatusChanged;
use App\Models\Disposisi;
use App\Models\DisposisiFollowUp;
use App\Models\RiwayatPerubahanData;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DisposisiFollowUpController extends Controller
{
    public function update(Request $request, Disposisi $disposisi, DisposisiFollowUp $followUp)
    {
        if ($followUp->user_id !== Auth::id() && !in_array(Auth::user()->role->kode, ['sekretaris', 'admin'])) {
            abort(403, 'Anda tidak berhak mengubah follow-up ini.');
        }

        $data = $request->validate([
            'keterangan' => 'required|string',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'hapus_lampiran' => 'nullable|array',
            'hapus_lampiran.*' => 'string',
        ]);

        $lampiran = is_array($followUp->lampiran) ? $followUp->lampiran : [];
        $hapus = $request->input('hapus_lampiran', []);

        if ($hapus) {
            $lampiran = array_values(array_filter($lampiran, function ($file) use ($hapus) {
                return !in_array($file['stored_path'] ?? null, $hapus);
            }));
        }

        $files = $request->file('lampiran');
        if ($files && is_array($files)) {
            foreach ($files as $file) {
                try {
                    if (!$file instanceof UploadedFile || !$file->isValid()) {
                        continue;
                    }
                    $originalName = $file->getClientOriginalName();
                    $mimeType = $file->getMimeType();
                    $fileSize = $file->getSize();
                    $ext = $file->getClientOriginalExtension() ?: 'bin';
                    $destDir = storage_path('app/public/disposisi');
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    $filename = Str::random(40) . '.' . $ext;
                    $file->move($destDir, $filename);
                    $lampiran[] = [
                        'original_name' => $originalName,
                        'stored_path' => 'disposisi/' . $filename,
                        'mime_type' => $mimeType,
                        'file_size' => $fileSize,
                    ];
                } catch (\Throwable $e) {
                    Log::error('Follow-up lampiran update failed', ['error' => $e->getMessage()]);
                }
            }
        }

        $followUp->update([
            'keterangan' => $data['keterangan'],
            'lampiran' => !empty($lampiran) ? $lampiran : null,
        ]);

        $perubahan = [];
        if ($followUp->getOriginal('keterangan') !== $data['keterangan']) {
            $perubahan['keterangan'] = [
                'label' => 'Keterangan Follow-up',
                'lama' => $followUp->getOriginal('keterangan') ?? '-',
                'baru' => $data['keterangan'],
            ];
        }
        $oldLampiranCount = is_array($followUp->getOriginal('lampiran')) ? count($followUp->getOriginal('lampiran')) : 0;
        $newLampiranCount = is_array($lampiran) ? count($lampiran) : 0;
        if ($oldLampiranCount !== $newLampiranCount) {
            $perubahan['lampiran'] = [
                'label' => 'Lampiran Follow-up',
                'lama' => $oldLampiranCount . ' file',
                'baru' => $newLampiranCount . ' file',
            ];
        }

        if (!empty($perubahan)) {
            $disposisi = $followUp->disposisi;
            RiwayatPerubahanData::create([
                'surat_masuk_id' => $disposisi->surat_masuk_id,
                'disposisi_id' => $disposisi->id,
                'dilakukan_oleh_id' => Auth::id(),
                'perubahan' => $perubahan,
                'catatan' => 'Follow-up diperbarui oleh ' . Auth::user()->name . '.',
            ]);
        }

        return back()->with('success', 'Follow-up berhasil diperbarui.');
    }

    public function store(Request $request, Disposisi $disposisi)
    {
        $data = $request->validate([
            'keterangan' => 'required|string',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $lampiran = [];
        $files = $request->file('lampiran');

        if ($files && is_array($files)) {
            foreach ($files as $file) {
                try {
                    if (!$file instanceof UploadedFile || !$file->isValid()) {
                        continue;
                    }
                    $originalName = $file->getClientOriginalName();
                    $mimeType = $file->getMimeType();
                    $fileSize = $file->getSize();
                    $ext = $file->getClientOriginalExtension() ?: 'bin';
                    $destDir = storage_path('app/public/disposisi');
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    $filename = Str::random(40) . '.' . $ext;
                    $file->move($destDir, $filename);
                    $path = 'disposisi/' . $filename;
                    $lampiran[] = [
                        'original_name' => $originalName,
                        'stored_path' => $path,
                        'mime_type' => $mimeType,
                        'file_size' => $fileSize,
                    ];
                } catch (\Throwable $e) {
                    Log::error('Follow-up lampiran upload failed', ['error' => $e->getMessage()]);
                }
            }
        }

        $disposisi->followUps()->create([
            'user_id' => Auth::id(),
            'keterangan' => $data['keterangan'],
            'lampiran' => !empty($lampiran) ? $lampiran : null,
        ]);

        $surat = $disposisi->suratMasuk;
        if (!in_array($surat->status->kode, ['completed', 'archived'])) {
            $oldStatusId = $surat->status_id;
            $surat->update([
                'status_id' => statusId('completed'),
                'completed_at' => now(),
            ]);

            event(new SuratStatusChanged(
                surat: $surat,
                statusDariId: $oldStatusId,
                statusKeId: statusId('completed'),
                dilakukanOleh: Auth::user(),
                catatan: 'Selesai setelah follow-up',
                disposisiId: $disposisi->id,
            ));
        }

        return back()->with('success', 'Follow-up berhasil ditambahkan.');
    }
}
