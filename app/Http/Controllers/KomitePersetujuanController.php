<?php

namespace App\Http\Controllers;

use App\Http\Requests\KomitePersetujuanRequest;
use App\Models\KomitePersetujuan;
use App\Models\Topik;
use App\Services\ArsipAksesService;
use App\Services\ArsipMediaService;
use Illuminate\Http\Request;

class KomitePersetujuanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(KomitePersetujuan::class, 'komite_persetujuan');
    }

    public function index(Request $request, ArsipAksesService $akses)
    {
        $query = $akses->scopeUntukUser(
            KomitePersetujuan::with('dicatatOleh')->withCount(['lampiran', 'fotoKegiatan']),
            $request->user()
        );

        if ($tahun = $request->get('tahun')) {
            $query->whereYear('tanggal_rapat', $tahun);
        }

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('judul', 'like', "%{$q}%")
                    ->orWhere('daftar_peserta', 'like', "%{$q}%")
                    ->orWhere('catatan_keputusan', 'like', "%{$q}%");
            });
        }

        $arsip = $query->latest('tanggal_rapat')->paginate($request->get('per_page', 25));
        $tahunList = KomitePersetujuan::query()
            ->pluck('tanggal_rapat')
            ->map(fn ($tanggal) => $tanggal?->format('Y'))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        return view('komite-persetujuan.index', compact('arsip', 'tahunList'));
    }

    public function trash(Request $request)
    {
        $this->authorize('viewAny', KomitePersetujuan::class);

        $query = KomitePersetujuan::onlyTrashed();

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('jenis_komite', 'like', "%{$q}%")
                    ->orWhere('perihal', 'like', "%{$q}%");
            });
        }

        $items = $query->latest('deleted_at')->paginate($request->get('per_page', 25));

        return view('komite-persetujuan.trash', compact('items'));
    }

    public function create()
    {
        $topiks = Topik::orderBy('nama')->get();
        return view('komite-persetujuan.form', compact('topiks'));
    }

    public function store(KomitePersetujuanRequest $request, ArsipMediaService $media)
    {
        $data = $request->validated();
        unset($data['lampiran'], $data['lampiran_keterangan'], $data['foto_kegiatan'], $data['foto_caption'], $data['hapus_lampiran'], $data['hapus_foto'], $data['topik_ids']);
        $data['dicatat_oleh_id'] = $request->user()->id;

        $komite = KomitePersetujuan::create($data);

        // Parse topik_ids from JSON string (Alpine.js hidden input)
        $topikIds = $request->input('topik_ids', []);
        if (is_string($topikIds)) {
            $topikIds = json_decode($topikIds, true) ?? [];
        }
        $komite->topiks()->sync(is_array($topikIds) ? $topikIds : []);

        $media->simpanLampiran($komite, $request, $request->user()->id);
        $media->simpanFoto($komite, $request, $request->user()->id);

        return redirect()->route('komite-persetujuan.show', $komite->uuid)
            ->with('success', 'Hasil komite persetujuan berhasil dicatat.');
    }

    public function show(KomitePersetujuan $komitePersetujuan)
    {
        $komitePersetujuan->load(['dicatatOleh', 'topiks', 'lampiran.diunggahOleh', 'fotoKegiatan', 'riwayatArsip.dilakukanOleh']);

        return view('komite-persetujuan.show', ['komite' => $komitePersetujuan]);
    }

    public function edit(KomitePersetujuan $komitePersetujuan)
    {
        $komitePersetujuan->load(['topiks', 'lampiran', 'fotoKegiatan']);
        $topiks = Topik::orderBy('nama')->get();
        $selectedTopiks = $komitePersetujuan->topiks->pluck('id')->toArray();

        return view('komite-persetujuan.form', ['komite' => $komitePersetujuan, 'topiks' => $topiks, 'selectedTopiks' => $selectedTopiks]);
    }

    public function update(KomitePersetujuanRequest $request, KomitePersetujuan $komitePersetujuan, ArsipMediaService $media)
    {
        $data = $request->validated();
        unset($data['lampiran'], $data['lampiran_keterangan'], $data['foto_kegiatan'], $data['foto_caption'], $data['hapus_lampiran'], $data['hapus_foto'], $data['topik_ids']);

        $komitePersetujuan->catatPerubahanData($data, $komitePersetujuan->getAttributes());
        $komitePersetujuan->update($data);

        // Parse topik_ids from JSON string (Alpine.js hidden input)
        $topikIds = $request->input('topik_ids', []);
        if (is_string($topikIds)) {
            $topikIds = json_decode($topikIds, true) ?? [];
        }
        $komitePersetujuan->topiks()->sync(is_array($topikIds) ? $topikIds : []);

        $media->hapusDipilih(
            $komitePersetujuan,
            $request->input('hapus_lampiran', []),
            $request->input('hapus_foto', [])
        );
        $media->simpanLampiran($komitePersetujuan, $request, $request->user()->id);
        $media->simpanFoto($komitePersetujuan, $request, $request->user()->id);

        return redirect()->route('komite-persetujuan.show', $komitePersetujuan->uuid)
            ->with('success', 'Hasil komite persetujuan berhasil diperbarui.');
    }

    public function destroy(KomitePersetujuan $komitePersetujuan, ArsipMediaService $media)
    {
        $media->hapusSemua($komitePersetujuan);
        $komitePersetujuan->delete();

        return redirect()->route('komite-persetujuan.index')
            ->with('success', 'Hasil komite persetujuan berhasil dihapus.');
    }

    public function restore(string $uuid)
    {
        $komite = KomitePersetujuan::withTrashed()->where('uuid', $uuid)->firstOrFail();
        $komite->restore();

        return redirect()->route('komite-persetujuan.show', $uuid)
            ->with('success', 'Komite Persetujuan berhasil dipulihkan.');
    }

    public function forceDelete(string $uuid)
    {
        $komite = KomitePersetujuan::withTrashed()->where('uuid', $uuid)->firstOrFail();
        $media = app(ArsipMediaService::class);
        $media->hapusSemua($komite);
        $komite->forceDelete();

        return redirect()->route('komite-persetujuan.index')
            ->with('success', 'Komite Persetujuan berhasil dihapus permanen.');
    }
}
