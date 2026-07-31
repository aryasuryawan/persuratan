<?php

namespace App\Http\Controllers;

use App\Http\Requests\KomitePersetujuanRequest;
use App\Models\KomitePersetujuan;
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

    public function create()
    {
        return view('komite-persetujuan.form');
    }

    public function store(KomitePersetujuanRequest $request, ArsipMediaService $media)
    {
        $data = $request->validated();
        unset($data['lampiran'], $data['lampiran_keterangan'], $data['foto_kegiatan'], $data['foto_caption'], $data['hapus_lampiran'], $data['hapus_foto']);
        $data['dicatat_oleh_id'] = $request->user()->id;
        $data['is_rahasia'] = $request->boolean('is_rahasia');

        $komite = KomitePersetujuan::create($data);

        $media->simpanLampiran($komite, $request, $request->user()->id);
        $media->simpanFoto($komite, $request, $request->user()->id);

        return redirect()->route('komite-persetujuan.show', $komite->uuid)
            ->with('success', 'Hasil komite persetujuan berhasil dicatat.');
    }

    public function show(KomitePersetujuan $komitePersetujuan)
    {
        $komitePersetujuan->load(['dicatatOleh', 'lampiran.diunggahOleh', 'fotoKegiatan', 'riwayatArsip.dilakukanOleh']);

        return view('komite-persetujuan.show', ['komite' => $komitePersetujuan]);
    }

    public function edit(KomitePersetujuan $komitePersetujuan)
    {
        $komitePersetujuan->load(['lampiran', 'fotoKegiatan']);

        return view('komite-persetujuan.form', ['komite' => $komitePersetujuan]);
    }

    public function update(KomitePersetujuanRequest $request, KomitePersetujuan $komitePersetujuan, ArsipMediaService $media)
    {
        $data = $request->validated();
        unset($data['lampiran'], $data['lampiran_keterangan'], $data['foto_kegiatan'], $data['foto_caption'], $data['hapus_lampiran'], $data['hapus_foto']);
        $data['is_rahasia'] = $request->boolean('is_rahasia');

        $komitePersetujuan->catatPerubahanData($data, $komitePersetujuan->getAttributes());
        $komitePersetujuan->update($data);

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
}
