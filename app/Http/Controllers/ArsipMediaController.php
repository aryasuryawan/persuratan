<?php

namespace App\Http\Controllers;

use App\Models\FotoKegiatan;
use App\Models\Lampiran;
use Illuminate\Support\Facades\Storage;

class ArsipMediaController extends Controller
{
    public function lampiran(Lampiran $lampiran)
    {
        $this->authorize('view', $lampiran->lampirkan);

        if (! Storage::disk('local')->exists($lampiran->path)) {
            abort(404);
        }

        return Storage::disk('local')->download($lampiran->path, $lampiran->nama_file);
    }

    public function foto(FotoKegiatan $foto)
    {
        $this->authorize('view', $foto->kegiatan);

        if (! Storage::disk('local')->exists($foto->path)) {
            abort(404);
        }

        return Storage::disk('local')->response($foto->path);
    }
}
