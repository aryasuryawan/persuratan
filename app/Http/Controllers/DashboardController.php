<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Services\SuratScopeService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(SuratScopeService $scope)
    {
        $user = Auth::user();

        $suratQuery = $scope->scopeForUser(SuratMasuk::query(), $user);

        $totalSuratMasuk = (clone $suratQuery)->count();
        $perluVerifikasi = (clone $suratQuery)->where('status_id', statusId('received'))->count();
        $selesai = (clone $suratQuery)->where('status_id', statusId('completed'))->count();
        $persenSelesai = $totalSuratMasuk > 0 ? round(($selesai / $totalSuratMasuk) * 100) : 0;

        $data = [
            'totalSuratMasuk' => $totalSuratMasuk,
            'perluVerifikasi' => $perluVerifikasi,
            'dalamProses' => (clone $suratQuery)->whereIn('status_id', [
                statusId('verified'),
                statusId('forwarded_to_leadership'),
                statusId('assigned'),
                statusId('handled_by_leadership'),
                statusId('in_progress'),
            ])->count(),
            'selesai' => $selesai,
            'persenSelesai' => $persenSelesai,
            'suratHariIni' => (clone $suratQuery)->whereDate('tanggal_diterima', today())->count(),
            'suratKemarin' => (clone $suratQuery)->whereDate('tanggal_diterima', today()->subDay())->count(),
            'suratTerbaru' => (clone $suratQuery)->with(['status', 'klasifikasi'])
                ->latest('tanggal_diterima')->take(5)->get(),
        ];

        return view('dashboard', $data);
    }

    public function dashboard2(SuratScopeService $scope)
    {
        $user = Auth::user();
        $suratQuery = $scope->scopeForUser(SuratMasuk::query(), $user);

        $totalSuratMasuk = (clone $suratQuery)->count();
        $perluVerifikasi = (clone $suratQuery)->where('status_id', statusId('received'))->count();
        $selesai = (clone $suratQuery)->where('status_id', statusId('completed'))->count();
        $dalamProses = (clone $suratQuery)->whereIn('status_id', [
            statusId('verified'),
            statusId('forwarded_to_leadership'),
            statusId('assigned'),
            statusId('handled_by_leadership'),
            statusId('in_progress'),
        ])->count();
        $suratKeluar = \App\Models\SuratKeluar::count();
        $suratHariIni = (clone $suratQuery)->whereDate('tanggal_diterima', today())->count();
        $suratKemarin = (clone $suratQuery)->whereDate('tanggal_diterima', today()->subDay())->count();
        $persenSelesai = $totalSuratMasuk > 0 ? round(($selesai / $totalSuratMasuk) * 100) : 0;

        // Daily counts for last 30 days
        $dailyLabels = [];
        $dailyCounts = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyLabels[] = $date->format('d M');
            $dailyCounts[] = (clone $suratQuery)->whereDate('tanggal_diterima', $date)->count();
        }

        // Status breakdown for donut
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
            $count = (clone $suratQuery)->where('status_id', statusId($kode))->count();
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

        $days = 30;
        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');
            $values[] = (clone $suratQuery)->whereDate('tanggal_diterima', $date)->count();
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }
}
