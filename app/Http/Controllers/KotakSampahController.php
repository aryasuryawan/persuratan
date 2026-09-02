<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\SuratKeputusan;
use App\Models\KomitePersetujuan;
use App\Models\NotulensiRapim;
use Illuminate\Http\Request;

class KotakSampahController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', SuratMasuk::class);

        $tab = $request->get('tab', 'semua');

        $items = collect();

        if (in_array($tab, ['semua', 'surat-masuk'])) {
            $sm = SuratMasuk::onlyTrashed();
            if ($q = $request->get('q')) {
                $sm->where(function ($sub) use ($q) {
                    $sub->where('nomor_agenda', 'like', "%{$q}%")
                        ->orWhere('pengirim', 'like', "%{$q}%")
                        ->orWhere('perihal', 'like', "%{$q}%");
                });
            }
            $items = $items->merge($sm->get()->map(fn($s) => [
                'type' => 'Surat Masuk',
                'type_route' => 'surat-masuk',
                'uuid' => $s->uuid,
                'judul' => $s->nomor_agenda . ' — ' . $s->pengirim,
                'perihal' => $s->perihal,
                'deleted_at' => $s->deleted_at,
                'restore_route' => route('penerimaan-surat.restore', $s->uuid),
                'force_delete_route' => route('penerimaan-surat.force-delete', $s->uuid),
            ]));
        }

        if (in_array($tab, ['semua', 'surat-keluar'])) {
            $sk = SuratKeluar::onlyTrashed();
            if ($q = $request->get('q')) {
                $sk->where(function ($sub) use ($q) {
                    $sub->where('nomor_surat', 'like', "%{$q}%")
                        ->orWhere('tujuan_surat', 'like', "%{$q}%")
                        ->orWhere('perihal', 'like', "%{$q}%");
                });
            }
            $items = $items->merge($sk->get()->map(fn($s) => [
                'type' => 'Surat Keluar',
                'type_route' => 'surat-keluar',
                'uuid' => $s->uuid,
                'judul' => $s->nomor_surat ?? $s->perihal,
                'perihal' => $s->perihal,
                'deleted_at' => $s->deleted_at,
                'restore_route' => route('surat-keluar.restore', $s->uuid),
                'force_delete_route' => route('surat-keluar.force-delete', $s->uuid),
            ]));
        }

        if (in_array($tab, ['semua', 'surat-keputusan'])) {
            $sk = SuratKeputusan::onlyTrashed();
            if ($q = $request->get('q')) {
                $sk->where(function ($sub) use ($q) {
                    $sub->where('nomor_sk', 'like', "%{$q}%")
                        ->orWhere('perihal', 'like', "%{$q}%");
                });
            }
            $items = $items->merge($sk->get()->map(fn($s) => [
                'type' => 'Surat Keputusan',
                'type_route' => 'surat-keputusan',
                'uuid' => $s->uuid,
                'judul' => $s->nomor_sk,
                'perihal' => $s->perihal,
                'deleted_at' => $s->deleted_at,
                'restore_route' => route('surat-keputusan.restore', $s->uuid),
                'force_delete_route' => route('surat-keputusan.force-delete', $s->uuid),
            ]));
        }

        if (in_array($tab, ['semua', 'komite'])) {
            $k = KomitePersetujuan::onlyTrashed();
            if ($q = $request->get('q')) {
                $k->where(function ($sub) use ($q) {
                    $sub->where('judul', 'like', "%{$q}%");
                });
            }
            $items = $items->merge($k->get()->map(fn($s) => [
                'type' => 'Komite Persetujuan',
                'type_route' => 'komite-persetujuan',
                'uuid' => $s->uuid,
                'judul' => $s->judul,
                'perihal' => $s->hasil ?? '-',
                'deleted_at' => $s->deleted_at,
                'restore_route' => route('komite-persetujuan.restore', $s->uuid),
                'force_delete_route' => route('komite-persetujuan.force-delete', $s->uuid),
            ]));
        }

        if (in_array($tab, ['semua', 'notulensi'])) {
            $n = NotulensiRapim::onlyTrashed();
            if ($q = $request->get('q')) {
                $n->where(function ($sub) use ($q) {
                    $sub->where('judul_rapat', 'like', "%{$q}%");
                });
            }
            $items = $items->merge($n->get()->map(fn($s) => [
                'type' => 'Notulensi Rapim',
                'type_route' => 'notulensi-rapim',
                'uuid' => $s->uuid,
                'judul' => $s->judul_rapat,
                'perihal' => $s->tanggal?->format('d/m/Y') ?? '-',
                'deleted_at' => $s->deleted_at,
                'restore_route' => route('notulensi-rapim.restore', $s->uuid),
                'force_delete_route' => route('notulensi-rapim.force-delete', $s->uuid),
            ]));
        }

        $items = $items->sortByDesc('deleted_at')->values();

        return view('kotak-sampah.index', compact('items', 'tab'));
    }
}
