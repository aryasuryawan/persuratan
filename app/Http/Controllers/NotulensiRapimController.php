<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotulensiRapimRequest;
use App\Models\NotulensiRapim;
use App\Models\Topik;
use App\Services\ArsipAksesService;
use App\Services\ArsipMediaService;
use Illuminate\Http\Request;

class NotulensiRapimController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(NotulensiRapim::class, 'notulensi_rapim');
    }

    public function index(Request $request, ArsipAksesService $akses)
    {
        $query = $akses->scopeUntukUser(
            NotulensiRapim::with('dicatatOleh')->withCount(['lampiran', 'fotoKegiatan']),
            $request->user()
        );

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
        $topiks = Topik::orderBy('nama')->get();
        return view('notulensi-rapim.form', compact('topiks'));
    }

    public function store(NotulensiRapimRequest $request, ArsipMediaService $media)
    {
        $data = $request->validated();
        unset($data['lampiran'], $data['lampiran_keterangan'], $data['foto_kegiatan'], $data['foto_caption'], $data['hapus_lampiran'], $data['hapus_foto'], $data['topik_ids']);
        $data['dicatat_oleh_id'] = $request->user()->id;

        $rapim = NotulensiRapim::create($data);

        // Parse topik_ids from JSON string (Alpine.js hidden input)
        $topikIds = $request->input('topik_ids', []);
        if (is_string($topikIds)) {
            $topikIds = json_decode($topikIds, true) ?? [];
        }
        $rapim->topiks()->sync(is_array($topikIds) ? $topikIds : []);

        $media->simpanLampiran($rapim, $request, $request->user()->id);
        $media->simpanFoto($rapim, $request, $request->user()->id);

        return redirect()->route('notulensi-rapim.show', $rapim->uuid)
            ->with('success', 'Notulensi rapat pimpinan berhasil dicatat.');
    }

    public function show(NotulensiRapim $notulensiRapim)
    {
        $notulensiRapim->load(['dicatatOleh', 'topiks', 'lampiran.diunggahOleh', 'fotoKegiatan', 'riwayatArsip.dilakukanOleh']);

        return view('notulensi-rapim.show', ['rapim' => $notulensiRapim]);
    }

    public function edit(NotulensiRapim $notulensiRapim)
    {
        $notulensiRapim->load(['topiks', 'lampiran', 'fotoKegiatan']);
        $topiks = Topik::orderBy('nama')->get();
        $selectedTopiks = $notulensiRapim->topiks->pluck('id')->toArray();

        return view('notulensi-rapim.form', ['rapim' => $notulensiRapim, 'topiks' => $topiks, 'selectedTopiks' => $selectedTopiks]);
    }

    public function update(NotulensiRapimRequest $request, NotulensiRapim $notulensiRapim, ArsipMediaService $media)
    {
        $data = $request->validated();
        unset($data['lampiran'], $data['lampiran_keterangan'], $data['foto_kegiatan'], $data['foto_caption'], $data['hapus_lampiran'], $data['hapus_foto'], $data['topik_ids']);

        $notulensiRapim->catatPerubahanData($data, $notulensiRapim->getAttributes());
        $notulensiRapim->update($data);

        // Parse topik_ids from JSON string (Alpine.js hidden input)
        $topikIds = $request->input('topik_ids', []);
        if (is_string($topikIds)) {
            $topikIds = json_decode($topikIds, true) ?? [];
        }
        $notulensiRapim->topiks()->sync(is_array($topikIds) ? $topikIds : []);

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

    public function trash(Request $request)
    {
        $this->authorize('viewAny', NotulensiRapim::class);

        $query = NotulensiRapim::onlyTrashed();

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('judul_rapat', 'like', "%{$q}%")
                    ->orWhere('ringkasan_pembahasan', 'like', "%{$q}%");
            });
        }

        $items = $query->latest('deleted_at')->paginate($request->get('per_page', 25));

        return view('notulensi-rapim.trash', compact('items'));
    }

    public function destroy(NotulensiRapim $notulensiRapim, ArsipMediaService $media)
    {
        $media->hapusSemua($notulensiRapim);
        $notulensiRapim->delete();

        return redirect()->route('notulensi-rapim.index')
            ->with('success', 'Notulensi rapat pimpinan berhasil dihapus.');
    }

    public function restore(string $uuid)
    {
        $rapim = NotulensiRapim::withTrashed()->where('uuid', $uuid)->firstOrFail();

        if (! request()->user()->isCreatorOrAdmin($rapim->dicatat_oleh_id)) {
            abort(403, 'Anda tidak berhak memulihkan record ini.');
        }

        $rapim->restore();

        return redirect()->route('notulensi-rapim.show', $uuid)
            ->with('success', 'Notulensi Rapim berhasil dipulihkan.');
    }

    public function forceDelete(string $uuid)
    {
        $rapim = NotulensiRapim::withTrashed()->where('uuid', $uuid)->firstOrFail();

        if (! request()->user()->isCreatorOrAdmin($rapim->dicatat_oleh_id)) {
            abort(403, 'Anda tidak berhak menghapus permanen record ini.');
        }

        $media = app(ArsipMediaService::class);
        $media->hapusSemua($rapim);
        $rapim->forceDelete();

        return redirect()->route('notulensi-rapim.index')
            ->with('success', 'Notulensi Rapim berhasil dihapus permanen.');
    }
}
