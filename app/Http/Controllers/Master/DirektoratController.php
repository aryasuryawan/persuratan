<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Direktorat;
use Illuminate\Http\Request;

class DirektoratController extends Controller
{
    public function index(Request $request)
    {
        $query = Direktorat::withCount('divisis');

        if ($q = $request->get('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('nama', 'like', "%{$q}%")
                    ->orWhere('kode', 'like', "%{$q}%");
            });
        }

        if (($aktif = $request->get('aktif')) !== null && $aktif !== '') {
            $query->where('aktif', $aktif);
        }

        $direktorats = $query->latest()->paginate($request->get('per_page', 25));

        return view('master.direktorats.index', compact('direktorats'));
    }

    public function create()
    {
        return view('master.direktorats.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:direktorats',
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        Direktorat::create($data);

        return redirect()->route('master.direktorats.index')
            ->with('success', 'Direktorat berhasil dibuat.');
    }

    public function edit(Direktorat $direktorat)
    {
        return view('master.direktorats.edit', compact('direktorat'));
    }

    public function update(Request $request, Direktorat $direktorat)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:direktorats,kode,' . $direktorat->id,
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        $direktorat->update($data);

        return redirect()->route('master.direktorats.index')
            ->with('success', 'Direktorat berhasil diperbarui.');
    }

    public function destroy(Direktorat $direktorat)
    {
        if ($direktorat->divisis()->exists()) {
            return back()->with('error', 'Direktorat masih memiliki divisi.');
        }

        $direktorat->delete();

        return redirect()->route('master.direktorats.index')
            ->with('success', 'Direktorat berhasil dihapus.');
    }
}