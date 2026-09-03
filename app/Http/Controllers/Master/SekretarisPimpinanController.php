<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SekretarisPimpinan;
use App\Models\User;
use Illuminate\Http\Request;

class SekretarisPimpinanController extends Controller
{
    public function index()
    {
        $data = SekretarisPimpinan::with(['sekretaris', 'pimpinan'])->latest()->paginate(request('per_page', 25));
        return view('master.sekretaris-pimpinan.index', compact('data'));
    }

    public function create()
    {
        $sekretaris = User::whereHas('role', fn($q) => $q->where('kode', 'sekretaris'))
            ->where('is_active', true)->get();
        $pimpinan = User::whereHas('role', fn($q) => $q->where('kode', 'pimpinan'))
            ->where('is_active', true)->get();

        return view('master.sekretaris-pimpinan.create', compact('sekretaris', 'pimpinan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sekretaris_id' => 'required|exists:users,id',
            'pimpinan_ids' => 'required|array',
            'pimpinan_ids.*' => 'exists:users,id',
            'is_active' => 'boolean',
        ]);

        $sekretarisId = $data['sekretaris_id'];
        $isActive = $request->boolean('is_active', true);
        $count = 0;

        foreach ($data['pimpinan_ids'] as $pimpinanId) {
            $exists = SekretarisPimpinan::where('sekretaris_id', $sekretarisId)
                ->where('pimpinan_id', $pimpinanId)->exists();
            if (!$exists) {
                SekretarisPimpinan::create([
                    'sekretaris_id' => $sekretarisId,
                    'pimpinan_id' => $pimpinanId,
                    'is_active' => $isActive,
                ]);
                $count++;
            }
        }

        return redirect()->route('master.sekretaris-pimpinan.index')
            ->with('success', "{$count} relasi berhasil dibuat.");
    }

    public function destroy(SekretarisPimpinan $sekretarisPimpinan)
    {
        abort_unless(request()->user()->isAdmin(), 403, 'Hanya administrator yang dapat menghapus data master.');

        $sekretarisPimpinan->delete();

        return redirect()->route('master.sekretaris-pimpinan.index')
            ->with('success', 'Relasi berhasil dihapus.');
    }
}
