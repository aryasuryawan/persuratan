<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\DisposisiFollowUp;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DisposisiFollowUpController extends Controller
{
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

        return back()->with('success', 'Follow-up berhasil ditambahkan.');
    }
}
