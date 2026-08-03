<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Services\SuratScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function index(Request $request, SuratScopeService $scope)
    {
        $user = Auth::user();

        $uuid = $request->get('surat');
        $surat = null;
        $riwayat = [];

        if ($uuid) {
            $surat = SuratMasuk::with(['klasifikasi', 'status', 'pembuat'])
                ->where('uuid', $uuid)->first();

            if ($surat) {
                $riwayat = $surat->riwayatStatus()
                    ->with(['statusDari', 'statusKe', 'dilakukanOleh', 'atasNamaPimpinan'])
                    ->orderBy('created_at')
                    ->get();
            }
        }

        $stats = [];
        $statuses = [
            'received' => ['label' => 'Diterima', 'color' => 'primary', 'icon' => 'mail'],
            'verified' => ['label' => 'Diverifikasi', 'color' => 'info', 'icon' => 'circle-check'],
            'forwarded_to_leadership' => ['label' => 'Ke Pimpinan', 'color' => 'warning', 'icon' => 'arrow-forward'],
            'assigned' => ['label' => 'Didisposisikan', 'color' => 'secondary', 'icon' => 'message-2'],
            'in_progress' => ['label' => 'Diproses', 'color' => 'info', 'icon' => 'clock-play'],
            'completed' => ['label' => 'Selesai', 'color' => 'success', 'icon' => 'circle-check'],
            'archived' => ['label' => 'Diarsipkan', 'color' => 'secondary', 'icon' => 'archive'],
        ];

        $baseQuery = $scope->scopeForUser(SuratMasuk::query(), $user);
        $ids = statusIds();

        $statusCounts = (clone $baseQuery)
            ->selectRaw('status_id, COUNT(*) as jumlah')
            ->groupBy('status_id')
            ->pluck('jumlah', 'status_id')
            ->mapWithKeys(fn ($c, $k) => [(int) $k => (int) $c]);

        foreach ($statuses as $kode => $config) {
            $stats[] = [
                'kode' => $kode,
                'label' => $config['label'],
                'count' => $statusCounts[$ids[$kode]] ?? 0,
                'color' => $config['color'],
                'icon' => $config['icon'],
                'description' => 'surat ' . strtolower($config['label']),
            ];
        }

        return view('tracking.index', compact('surat', 'riwayat', 'stats'));
    }

    public function chart(SuratScopeService $scope)
    {
        $user = auth()->user();
        $query = $scope->scopeForUser(SuratMasuk::query(), $user)
            ->where('tanggal_diterima', '>=', now()->subDays(30));

        $data = $query->selectRaw('DATE(tanggal_diterima) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $allDates = collect();
        for ($i = 29; $i >= 0; $i--) {
            $allDates->push(now()->subDays($i)->format('Y-m-d'));
        }

        $filled = $allDates->mapWithKeys(function ($date) use ($data) {
            $match = $data->firstWhere('date', $date);
            return [$date => $match ? (int) $match->total : 0];
        });

        return response()->json([
            'labels' => $filled->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m')),
            'values' => $filled->values(),
        ]);
    }
}
