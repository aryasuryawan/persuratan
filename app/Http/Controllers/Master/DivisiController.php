<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use Illuminate\Http\Request;

class DivisiController extends Controller
{
    public function index(Request $request)
    {
        $query = Divisi::with('direktorat')->withCount('departemens');

        if ($q = $request->get('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('nama', 'like', "%{$q}%")
                    ->orWhere('kode', 'like', "%{$q}%");
            });
        }

        if ($direktoratId = $request->get('direktorat_id')) {
            $query->where('direktorat_id', $direktoratId);
        }

        if (($aktif = $request->get('aktif')) !== null && $aktif !== '') {
            $query->where('aktif', $aktif);
        }

        $divisis = $query->latest()->paginate($request->get('per_page', 25));

        $direktorats = \App\Models\Direktorat::where('aktif', true)->get();

        return view('master.divisis.index', compact('divisis', 'direktorats'));
    }

    public function create()
    {
        $direktorats = \App\Models\Direktorat::where('aktif', true)->pluck('nama', 'id');
        return view('master.divisis.create', compact('direktorats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'direktorat_id' => 'required|exists:direktorats,id',
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:divisis',
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        Divisi::create($data);

        return redirect()->route('master.divisis.index')
            ->with('success', 'Divisi berhasil dibuat.');
    }

    public function edit(Divisi $divisi)
    {
        $direktorats = \App\Models\Direktorat::where('aktif', true)->pluck('nama', 'id');
        return view('master.divisis.edit', compact('divisi', 'direktorats'));
    }

    public function update(Request $request, Divisi $divisi)
    {
        $data = $request->validate([
            'direktorat_id' => 'required|exists:direktorats,id',
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:divisis,kode,' . $divisi->id,
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        $divisi->update($data);

        return redirect()->route('master.divisis.index')
            ->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy(Divisi $divisi)
    {
        if ($divisi->departemens()->exists()) {
            return back()->with('error', 'Divisi masih memiliki departemen.');
        }

        $divisi->delete();

        return redirect()->route('master.divisis.index')
            ->with('success', 'Divisi berhasil dihapus.');
    }
}