<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratKeputusanRequest;
use App\Models\SuratKeputusan;
use App\Models\Topik;
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

    public function trash(Request $request)
    {
        $this->authorize('viewAny', SuratKeputusan::class);

        $query = SuratKeputusan::onlyTrashed();

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_sk', 'like', "%{$q}%")
                    ->orWhere('perihal', 'like', "%{$q}%");
            });
        }

        $items = $query->latest('deleted_at')->paginate($request->get('per_page', 25));

        return view('surat-keputusan.trash', compact('items'));
    }

    public function create()
    {
        $topiks = Topik::orderBy('nama')->get();
        return view('surat-keputusan.form', compact('topiks'));
    }

    public function store(SuratKeputusanRequest $request, ArsipMediaService $media)
    {
        $data = $request->safe()->except([
            'lampiran', 'lampiran_keterangan',
            'foto_kegiatan', 'foto_caption',
            'hapus_lampiran', 'hapus_foto',
            'topik_ids',
        ]);
        $data['dicatat_oleh_id'] = $request->user()->id;

        $sk = SuratKeputusan::create($data);

        // Parse topik_ids from JSON string (Alpine.js hidden input)
        $topikIds = $request->input('topik_ids', []);
        if (is_string($topikIds)) {
            $topikIds = json_decode($topikIds, true) ?? [];
        }
        $sk->topiks()->sync(is_array($topikIds) ? $topikIds : []);

        $media->simpanLampiran($sk, $request, $request->user()->id);
        $media->simpanFoto($sk, $request, $request->user()->id);

        return redirect()->route('surat-keputusan.show', $sk->uuid)
            ->with('success', 'Surat Keputusan berhasil dicatat.');
    }

    public function show(SuratKeputusan $suratKeputusan)
    {
        $suratKeputusan->load(['dicatatOleh', 'topiks', 'lampiran.diunggahOleh', 'fotoKegiatan', 'riwayatArsip.dilakukanOleh']);

        return view('surat-keputusan.show', ['sk' => $suratKeputusan]);
    }

    public function edit(SuratKeputusan $suratKeputusan)
    {
        $suratKeputusan->load(['topiks', 'lampiran', 'fotoKegiatan']);
        $topiks = Topik::orderBy('nama')->get();
        $selectedTopiks = $suratKeputusan->topiks->pluck('id')->toArray();

        return view('surat-keputusan.form', ['sk' => $suratKeputusan, 'topiks' => $topiks, 'selectedTopiks' => $selectedTopiks]);
    }

    public function update(SuratKeputusanRequest $request, SuratKeputusan $suratKeputusan, ArsipMediaService $media)
    {
        $data = $request->validated();
        unset($data['lampiran'], $data['lampiran_keterangan'], $data['foto_kegiatan'], $data['foto_caption'], $data['hapus_lampiran'], $data['hapus_foto'], $data['topik_ids']);

        $suratKeputusan->catatPerubahanData($data, $suratKeputusan->getAttributes());
        $suratKeputusan->update($data);

        // Parse topik_ids from JSON string (Alpine.js hidden input)
        $topikIds = $request->input('topik_ids', []);
        if (is_string($topikIds)) {
            $topikIds = json_decode($topikIds, true) ?? [];
        }
        $suratKeputusan->topiks()->sync(is_array($topikIds) ? $topikIds : []);

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
        // Clean up media files before soft delete
        $media->hapusSemua($suratKeputusan);
        $suratKeputusan->delete();

        return redirect()->route('surat-keputusan.index')
            ->with('success', 'Surat Keputusan berhasil dihapus.');
    }

    public function restore(string $uuid)
    {
        $surat = SuratKeputusan::withTrashed()->where('uuid', $uuid)->firstOrFail();
        $surat->restore();

        return redirect()->route('surat-keputusan.show', $uuid)
            ->with('success', 'Surat Keputusan berhasil dipulihkan.');
    }

    public function forceDelete(string $uuid)
    {
        $surat = SuratKeputusan::withTrashed()->where('uuid', $uuid)->firstOrFail();
        $media = app(ArsipMediaService::class);
        $media->hapusSemua($surat);
        $surat->forceDelete();

        return redirect()->route('surat-keputusan.index')
            ->with('success', 'Surat Keputusan berhasil dihapus permanen.');
    }
}
