<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\KodeSurat;
use Illuminate\Http\Request;

class KodeSuratController extends Controller
{
    public function index(Request $request)
    {
        $query = KodeSurat::query();

        if ($q = $request->get('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('kode', 'like', "%{$q}%")
                    ->orWhere('nama', 'like', "%{$q}%");
            });
        }

        if (($aktif = $request->get('aktif')) !== null && $aktif !== '') {
            $query->where('aktif', $aktif);
        }

        $kodeSurats = $query->latest()->paginate($request->get('per_page', 25));

        return view('master.kode-surats.index', compact('kodeSurats'));
    }

    public function create()
    {
        return view('master.kode-surats.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:kode_surats',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        KodeSurat::create($data);

        return redirect()->route('master.kode-surats.index')
            ->with('success', 'Kode surat berhasil dibuat.');
    }

    public function edit(KodeSurat $kodeSurat)
    {
        return view('master.kode-surats.edit', compact('kodeSurat'));
    }

    public function update(Request $request, KodeSurat $kodeSurat)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:kode_surats,kode,' . $kodeSurat->id,
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        $kodeSurat->update($data);

        return redirect()->route('master.kode-surats.index')
            ->with('success', 'Kode surat berhasil diperbarui.');
    }

    public function destroy(KodeSurat $kodeSurat)
    {
        abort_unless(request()->user()->isAdmin(), 403, 'Hanya administrator yang dapat menghapus data master.');

        $kodeSurat->delete();

        return redirect()->route('master.kode-surats.index')
            ->with('success', 'Kode surat berhasil dihapus.');
    }
}
