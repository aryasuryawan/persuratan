<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotulensiRapimRequest;
use App\Models\NotulensiRapim;
use App\Services\ArsipMediaService;
use Illuminate\Http\Request;

class NotulensiRapimController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(NotulensiRapim::class, 'notulensi_rapim');
    }

    public function index(Request $request)
    {
        $query = NotulensiRapim::with('dicatatOleh')
            ->withCount(['lampiran', 'fotoKegiatan']);

        if ($tahun = $request->get('tahun')) {
            $query->whereYear('tanggal', $tahun);
        }

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('judul_rapat', 'like', "%{$q}%")
                    ->orWhere('daftar_peserta', 'like', "%{$q}%")
                    ->orWhere('ringkasan_pembahasan', 'like', "%{$q}%");
            });
        }

        $arsip = $query->latest('tanggal')->paginate($request->get('per_page', 25));
        $tahunList = NotulensiRapim::query()
            ->pluck('tanggal')
            ->map(fn ($tanggal) => $tanggal?->format('Y'))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        return view('notulensi-rapim.index', compact('arsip', 'tahunList'));
    }

    public function create()
    {
        return view('notulensi-rapim.form');
    }

    public function store(NotulensiRapimRequest $request, ArsipMediaService $media)
    {
        $data = $request->safe()->except([
            'lampiran', 'lampiran_keterangan',
            'foto_kegiatan', 'foto_caption',
            'hapus_lampiran', 'hapus_foto',
        ]);
        $data['dicatat_oleh_id'] = $request->user()->id;

        $rapim = NotulensiRapim::create($data);

        $media->simpanLampiran($rapim, $request, $request->user()->id);
        $media->simpanFoto($rapim, $request, $request->user()->id);

        return redirect()->route('notulensi-rapim.show', $rapim->uuid)
            ->with('success', 'Notulensi rapat pimpinan berhasil dicatat.');
    }

    public function show(NotulensiRapim $notulensiRapim)
    {
        $notulensiRapim->load(['dicatatOleh', 'lampiran.diunggahOleh', 'fotoKegiatan']);

        return view('notulensi-rapim.show', ['rapim' => $notulensiRapim]);
    }

    public function edit(NotulensiRapim $notulensiRapim)
    {
        $notulensiRapim->load(['lampiran', 'fotoKegiatan']);

        return view('notulensi-rapim.form', ['rapim' => $notulensiRapim]);
    }

    public function update(NotulensiRapimRequest $request, NotulensiRapim $notulensiRapim, ArsipMediaService $media)
    {
        $data = $request->safe()->except([
            'lampiran', 'lampiran_keterangan',
            'foto_kegiatan', 'foto_caption',
            'hapus_lampiran', 'hapus_foto',
        ]);
        $notulensiRapim->update($data);

        $media->hapusDipilih(
            $notulensiRapim,
            $request->input('hapus_lampiran', []),
            $request->input('hapus_foto', [])
        );
        $media->simpanLampiran($notulensiRapim, $request, $request->user()->id);
        $media->simpanFoto($notulensiRapim, $request, $request->user()->id);

        return redirect()->route('notulensi-rapim.show', $notulensiRapim->uuid)
            ->with('success', 'Notulensi rapat pimpinan berhasil diperbarui.');
    }

    public function destroy(NotulensiRapim $notulensiRapim, ArsipMediaService $media)
    {
        $media->hapusSemua($notulensiRapim);
        $notulensiRapim->delete();

        return redirect()->route('notulensi-rapim.index')
            ->with('success', 'Notulensi rapat pimpinan berhasil dihapus.');
    }
}
