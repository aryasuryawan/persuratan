<?php

namespace App\Http\Controllers;

use App\Models\KomitePersetujuan;
use App\Models\NotulensiRapim;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Policies\KomitePersetujuanPolicy;
use App\Policies\NotulensiRapimPolicy;
use App\Policies\SuratKeputusanPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArsipAksesController extends Controller
{
    private const MODELS = [
        'surat-keputusan' => SuratKeputusan::class,
        'komite-persetujuan' => KomitePersetujuan::class,
        'notulensi-rapim' => NotulensiRapim::class,
    ];

    public function assignAkses(Request $request)
    {
        $arsip = $this->routeModel();

        $this->authorize('assignAkses', $arsip);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $arsip->aksesManual()->firstOrCreate(
            ['user_id' => $request->input('user_id')],
            ['level' => 'view', 'diberikan_oleh_id' => $request->user()->id],
        );

        return response()->json(['message' => 'Akses berhasil diberikan.']);
    }

    public function listAkses(Request $request)
    {
        $arsip = $this->routeModel();

        $this->authorize('view', $arsip);

        $akses = $arsip->aksesManual()->with(['user', 'diberikanOleh'])->get();

        return response()->json($akses->map(function ($a) {
            return [
                'user_id' => $a->user_id,
                'user_name' => $a->user?->name,
                'user_email' => $a->user?->email,
                'diberikan_oleh_name' => $a->diberikanOleh?->name,
                'created_at_formatted' => $a->created_at?->format('d/m/Y H:i'),
            ];
        }));
    }

    public function removeAkses(Request $request, $userId)
    {
        $arsip = $this->routeModel();

        $this->authorize('assignAkses', $arsip);

        $arsip->aksesManual()->where('user_id', $userId)->delete();

        return response()->json(['message' => 'Akses berhasil dicabut.']);
    }

    private function routeModel(): \Illuminate\Database\Eloquent\Model
    {
        $modul = request()->route('modul');
        $uuid = request()->route('uuid');

        $class = self::MODELS[$modul];

        return $class::where('uuid', $uuid)->firstOrFail();
    }
}
