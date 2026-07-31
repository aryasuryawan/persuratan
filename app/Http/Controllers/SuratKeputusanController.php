<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratKeputusanRequest;
use App\Models\SuratKeputusan;
use App\Services\ArsipAksesService;
use App\Services\ArsipMediaService;
use Illuminate\Http\Request;

class SuratKeputusanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SuratKeputusan::class, 'surat_keputusan');
    }

    public function index(Request $request, ArsipAksesService $akses)
    {
        $query = $akses->scopeUntukUser(
            SuratKeputusan::with('dicatatOleh')->withCount(['lampiran', 'fotoKegiatan']),
            $request->user()
        );

        if ($tahun = $request->get('tahun')) {
            $query->whereYear('tanggal_sk', $tahun);
        }

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_sk', 'like', "%{$q}%")
                    ->orWhere('perihal', 'like', "%{$q}%")
                    ->orWhere('kategori', 'like', "%{$q}%");
            });
        }

        $arsip = $query->latest('tanggal_sk')->paginate($request->get('per_page', 25));
        $tahunList = SuratKeputusan::query()
            ->pluck('tanggal_sk')
            ->map(fn ($tanggal) => $tanggal?->format('Y'))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        return view('surat-keputusan.index', compact('arsip', 'tahunList'));
    }

    public function create()
    {
        return view('surat-keputusan.form');
    }

    public function store(SuratKeputusanRequest $request, ArsipMediaService $media)
    {
        $data = $request->safe()->except([
            'lampiran', 'lampiran_keterangan',
            'foto_kegiatan', 'foto_caption',
            'hapus_lampiran', 'hapus_foto',
        ]);
        $data['dicatat_oleh_id'] = $request->user()->id;
        $data['is_rahasia'] = $request->boolean('is_rahasia');

        $sk = SuratKeputusan::create($data);

        $media->simpanLampiran($sk, $request, $request->user()->id);
        $media->simpanFoto($sk, $request, $request->user()->id);

        return redirect()->route('surat-keputusan.show', $sk->uuid)
            ->with('success', 'Surat Keputusan berhasil dicatat.');
    }

    public function show(SuratKeputusan $suratKeputusan)
    {
        $suratKeputusan->load(['dicatatOleh', 'lampiran.diunggahOleh', 'fotoKegiatan', 'riwayatArsip.dilakukanOleh']);

        return view('surat-keputusan.show', ['sk' => $suratKeputusan]);
    }

    public function edit(SuratKeputusan $suratKeputusan)
    {
        $suratKeputusan->load(['lampiran', 'fotoKegiatan']);

        return view('surat-keputusan.form', ['sk' => $suratKeputusan]);
    }

    public function update(SuratKeputusanRequest $request, SuratKeputusan $suratKeputusan, ArsipMediaService $media)
    {
        $data = $request->validated();
        unset($data['lampiran'], $data['lampiran_keterangan'], $data['foto_kegiatan'], $data['foto_caption'], $data['hapus_lampiran'], $data['hapus_foto']);
        $data['is_rahasia'] = $request->boolean('is_rahasia');

        $suratKeputusan->catatPerubahanData($data, $suratKeputusan->getAttributes());
        $suratKeputusan->update($data);

        $media->hapusDipilih(
            $suratKeputusan,
            $request->input('hapus_lampiran', []),
            $request->input('hapus_foto', [])
        );
        $media->simpanLampiran($suratKeputusan, $request, $request->user()->id);
        $media->simpanFoto($suratKeputusan, $request, $request->user()->id);

        return redirect()->route('surat-keputusan.show', $suratKeputusan->uuid)
            ->with('success', 'Surat Keputusan berhasil diperbarui.');
    }

    public function destroy(SuratKeputusan $suratKeputusan, ArsipMediaService $media)
    {
        $media->hapusSemua($suratKeputusan);
        $suratKeputusan->delete();

        return redirect()->route('surat-keputusan.index')
            ->with('success', 'Surat Keputusan berhasil dihapus.');
    }
}
