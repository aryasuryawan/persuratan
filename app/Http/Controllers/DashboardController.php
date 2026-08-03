<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\KomitePersetujuan;
use App\Models\NotulensiRapim;
use App\Models\SuratKeputusan;
use App\Services\SuratScopeService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(SuratScopeService $scope)
    {
        $user = Auth::user();

        $suratQuery = $scope->scopeForUser(SuratMasuk::query(), $user);
        $ids = statusIds();

        $statusCounts = (clone $suratQuery)
            ->selectRaw('status_id, COUNT(*) as jumlah')
            ->groupBy('status_id')
            ->pluck('jumlah', 'status_id')
            ->mapWithKeys(fn ($c, $k) => [(int) $k => (int) $c]);

        $totalSuratMasuk = $statusCounts->sum();
        $perluVerifikasi = $statusCounts[$ids['received']] ?? 0;
        $selesai = $statusCounts[$ids['completed']] ?? 0;
        $persenSelesai = $totalSuratMasuk > 0 ? round(($selesai / $totalSuratMasuk) * 100) : 0;

        $dalamProses = collect([
            $ids['verified'],
            $ids['forwarded_to_leadership'],
            $ids['assigned'],
            $ids['handled_by_leadership'],
            $ids['in_progress'],
        ])->sum(fn ($id) => $statusCounts[$id] ?? 0);

        $daily = (clone $suratQuery)
            ->whereBetween('tanggal_diterima', [today()->subDay(), today()])
            ->selectRaw('tanggal_diterima, COUNT(*) as jumlah')
            ->groupBy('tanggal_diterima')
            ->pluck('jumlah', 'tanggal_diterima')
            ->map(fn ($c) => (int) $c);

        $data = [
            'totalSuratMasuk' => $totalSuratMasuk,
            'perluVerifikasi' => $perluVerifikasi,
            'dalamProses' => $dalamProses,
            'selesai' => $selesai,
            'persenSelesai' => $persenSelesai,
            'suratHariIni' => $daily[today()->toDateString()] ?? 0,
            'suratKemarin' => $daily[today()->subDay()->toDateString()] ?? 0,
            'suratTerbaru' => (clone $suratQuery)->with(['status', 'klasifikasi'])
                ->latest('tanggal_diterima')->take(5)->get(),
            'suratKeluar' => SuratKeluar::query()->withoutTrashed()->count(),
            'komitePersetujuan' => KomitePersetujuan::count(),
            'notulensiRapim' => NotulensiRapim::count(),
            'suratKeputusan' => SuratKeputusan::count(),
            'arsipTerbaru' => collect([
                ...KomitePersetujuan::query()->latest('tanggal_rapat')->limit(3)->get()->map(fn ($r) => [
                    'judul' => $r->judul,
                    'tanggal' => $r->tanggal_rapat,
                    'modul' => 'Komite Persetujuan',
                    'route' => route('komite-persetujuan.show', $r->uuid),
                ]),
                ...NotulensiRapim::query()->latest('tanggal')->limit(3)->get()->map(fn ($r) => [
                    'judul' => $r->judul_rapat,
                    'tanggal' => $r->tanggal,
                    'modul' => 'Notulensi RAPIM',
                    'route' => route('notulensi-rapim.show', $r->uuid),
                ]),
                ...SuratKeputusan::query()->latest('tanggal_sk')->limit(3)->get()->map(fn ($r) => [
                    'judul' => $r->perihal,
                    'tanggal' => $r->tanggal_sk,
                    'modul' => 'Surat Keputusan',
                    'route' => route('surat-keputusan.show', $r->uuid),
                ]),
            ])->sortByDesc('tanggal')->take(5)->values(),
        ];

        return view('dashboard', $data);
    }

    public function dashboard2(SuratScopeService $scope)
    {
        $user = Auth::user();
        $suratQuery = $scope->scopeForUser(SuratMasuk::query(), $user);
        $ids = statusIds();

        $statusCounts = (clone $suratQuery)
            ->selectRaw('status_id, COUNT(*) as jumlah')
            ->groupBy('status_id')
            ->pluck('jumlah', 'status_id')
            ->mapWithKeys(fn ($c, $k) => [(int) $k => (int) $c]);

        $totalSuratMasuk = $statusCounts->sum();
        $perluVerifikasi = $statusCounts[$ids['received']] ?? 0;
        $selesai = $statusCounts[$ids['completed']] ?? 0;
        $dalamProses = collect([
            $ids['verified'],
            $ids['forwarded_to_leadership'],
            $ids['assigned'],
            $ids['handled_by_leadership'],
            $ids['in_progress'],
        ])->sum(fn ($id) => $statusCounts[$id] ?? 0);

        $suratKeluar = \App\Models\SuratKeluar::count();

        // Daily counts for last 30 days - single GROUP BY
        $start = Carbon::today()->subDays(29);
        $daily = (clone $suratQuery)
            ->whereBetween('tanggal_diterima', [$start, today()])
            ->selectRaw('tanggal_diterima, COUNT(*) as jumlah')
            ->groupBy('tanggal_diterima')
            ->pluck('jumlah', 'tanggal_diterima')
            ->map(fn ($c) => (int) $c);

        $suratHariIni = $daily[today()->toDateString()] ?? 0;
        $suratKemarin = $daily[today()->subDay()->toDateString()] ?? 0;
        $persenSelesai = $totalSuratMasuk > 0 ? round(($selesai / $totalSuratMasuk) * 100) : 0;

        $dailyLabels = [];
        $dailyCounts = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyLabels[] = $date->format('d M');
            $dailyCounts[] = $daily[$date->toDateString()] ?? 0;
        }

        // Status breakdown for donut - derived from single grouped query
        $statusData = collect();
        $statusKodeMap = [
            'received' => 'Diterima',
            'verified' => 'Diverifikasi',
            'forwarded_to_leadership' => 'Diteruskan',
            'assigned' => 'Ditugaskan',
            'in_progress' => 'Diproses',
            'completed' => 'Selesai',
            'archived' => 'Diarsipkan',
        ];
        foreach ($statusKodeMap as $kode => $label) {
            $count = $statusCounts[$ids[$kode]] ?? 0;
            if ($count > 0) {
                $statusData->push(['label' => $label, 'count' => $count]);
            }
        }

        // Recent letters for table
        $suratTerbaru = (clone $suratQuery)->with(['status', 'klasifikasi'])
            ->latest('tanggal_diterima')->take(6)->get();

        return view('dashboard2', [
            'totalSuratMasuk' => $totalSuratMasuk,
            'perluVerifikasi' => $perluVerifikasi,
            'dalamProses' => $dalamProses,
            'selesai' => $selesai,
            'persenSelesai' => $persenSelesai,
            'suratKeluar' => $suratKeluar,
            'suratHariIni' => $suratHariIni,
            'suratKemarin' => $suratKemarin,
            'dailyLabels' => $dailyLabels,
            'dailyCounts' => $dailyCounts,
            'statusData' => $statusData,
            'suratTerbaru' => $suratTerbaru,
        ]);
    }

    public function chart()
    {
        $user = Auth::user();
        $suratQuery = app(SuratScopeService::class)->scopeForUser(SuratMasuk::query(), $user);

        $start = Carbon::today()->subDays(29);
        $daily = (clone $suratQuery)
            ->whereBetween('tanggal_diterima', [$start, today()])
            ->selectRaw('tanggal_diterima, COUNT(*) as jumlah')
            ->groupBy('tanggal_diterima')
            ->pluck('jumlah', 'tanggal_diterima')
            ->map(fn ($c) => (int) $c);

        $labels = [];
        $values = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');
            $values[] = $daily[$date->toDateString()] ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }
}
