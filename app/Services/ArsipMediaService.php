<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArsipMediaService
{
    public function simpanLampiran(Model $parent, Request $request, int $userId): void
    {
        $files = $request->file('lampiran');
        if (! $files) {
            return;
        }

        $keterangan = $request->input('lampiran_keterangan', []);
        $dir = $this->direktori($parent, 'lampiran');

        foreach ($files as $i => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            try {
                $namaFile = $file->getClientOriginalName();
                $ukuranBytes = $file->getSize();
                $tipeFile = strtolower($file->getClientOriginalExtension() ?: 'bin');
                $filename = Str::random(40).'.'.$tipeFile;
                $path = $this->pindahkan($file, $dir, $filename);
            } catch (\Throwable $e) {
                Log::error('Arsip lampiran upload failed', ['error' => $e->getMessage()]);

                continue;
            }

            $parent->lampiran()->create([
                'nama_file' => $namaFile,
                'path' => $path,
                'tipe_file' => $tipeFile,
                'ukuran_bytes' => $ukuranBytes,
                'keterangan' => $keterangan[$i] ?? null,
                'diunggah_oleh_id' => $userId,
            ]);
        }
    }

    public function simpanFoto(Model $parent, Request $request, int $userId): void
    {
        $files = $request->file('foto_kegiatan');
        if (! $files) {
            return;
        }

        $caption = $request->input('foto_caption');
        $dir = $this->direktori($parent, 'foto');
        $urutan = $parent->fotoKegiatan()->count();

        foreach ($files as $i => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            try {
                $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $filename = Str::random(40).'.'.$ext;
                $path = $this->pindahkan($file, $dir, $filename);
            } catch (\Throwable $e) {
                Log::error('Arsip foto upload failed', ['error' => $e->getMessage()]);

                continue;
            }

            $parent->fotoKegiatan()->create([
                'path' => $path,
                'caption' => $caption,
                'urutan' => $urutan++,
                'diunggah_oleh_id' => $userId,
            ]);
        }
    }

    public function hapusDipilih(Model $parent, array $lampiranIds = [], array $fotoIds = []): void
    {
        foreach ($parent->lampiran()->whereIn('id', $lampiranIds)->get() as $lampiran) {
            Storage::disk('public')->delete($lampiran->path);
            $lampiran->delete();
        }

        foreach ($parent->fotoKegiatan()->whereIn('id', $fotoIds)->get() as $foto) {
            Storage::disk('public')->delete($foto->path);
            $foto->delete();
        }
    }

    public function hapusSemua(Model $parent): void
    {
        foreach ($parent->lampiran as $lampiran) {
            Storage::disk('public')->delete($lampiran->path);
        }
        foreach ($parent->fotoKegiatan as $foto) {
            Storage::disk('public')->delete($foto->path);
        }

        $parent->lampiran()->delete();
        $parent->fotoKegiatan()->delete();
    }

    private function direktori(Model $parent, string $subdir): string
    {
        return Str::snake(class_basename($parent)).'/'.$subdir;
    }

    private function pindahkan(UploadedFile $file, string $dir, string $filename): string
    {
        $destDir = Storage::disk('public')->path($dir);
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $file->move($destDir, $filename);

        return $dir.'/'.$filename;
    }
}
