<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use Illuminate\Http\Request;

class DepartemenController extends Controller
{
    public function index(Request $request)
    {
        $query = Departemen::with('divisi.direktorat');

        if ($q = $request->get('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('nama', 'like', "%{$q}%")
                    ->orWhere('kode', 'like', "%{$q}%");
            });
        }

        if ($divisiId = $request->get('divisi_id')) {
            $query->where('divisi_id', $divisiId);
        }

        if (($aktif = $request->get('aktif')) !== null && $aktif !== '') {
            $query->where('aktif', $aktif);
        }

        $departemens = $query->latest()->paginate($request->get('per_page', 25));

        $divisis = \App\Models\Divisi::with('direktorat')->whereHas('direktorat', fn($q) => $q->where('aktif', true))->get();

        return view('master.departemens.index', compact('departemens', 'divisis'));
    }

    public function create()
    {
        $divisis = \App\Models\Divisi::with('direktorat')->whereHas('direktorat', fn($q) => $q->where('aktif', true))->get();
        return view('master.departemens.create', compact('divisis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'divisi_id' => 'required|exists:divisis,id',
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:departemens',
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        Departemen::create($data);

        return redirect()->route('master.departemens.index')
            ->with('success', 'Departemen berhasil dibuat.');
    }

    public function edit(Departemen $departemen)
    {
        $divisis = \App\Models\Divisi::with('direktorat')->whereHas('direktorat', fn($q) => $q->where('aktif', true))->get();
        return view('master.departemens.edit', compact('departemen', 'divisis'));
    }

    public function update(Request $request, Departemen $departemen)
    {
        $data = $request->validate([
            'divisi_id' => 'required|exists:divisis,id',
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:departemens,kode,' . $departemen->id,
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        $departemen->update($data);

        return redirect()->route('master.departemens.index')
            ->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Departemen $departemen)
    {
        abort_unless(request()->user()->isAdmin(), 403, 'Hanya administrator yang dapat menghapus data master.');

        $departemen->delete();

        return redirect()->route('master.departemens.index')
            ->with('success', 'Departemen berhasil dihapus.');
    }
}