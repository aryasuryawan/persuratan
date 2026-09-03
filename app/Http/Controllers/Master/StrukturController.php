<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Struktur;
use Illuminate\Http\Request;

class StrukturController extends Controller
{
    public function index(Request $request)
    {
        $query = Struktur::query();

        if ($q = $request->get('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('nama', 'like', "%{$q}%")
                    ->orWhere('kode', 'like', "%{$q}%");
            });
        }

        if (($aktif = $request->get('aktif')) !== null && $aktif !== '') {
            $query->where('aktif', $aktif);
        }

        $strukturs = $query->latest()->paginate($request->get('per_page', 25));

        return view('master.strukturs.index', compact('strukturs'));
    }

    public function create()
    {
        return view('master.strukturs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:strukturs',
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        Struktur::create($data);

        return redirect()->route('master.strukturs.index')
            ->with('success', 'Struktur berhasil dibuat.');
    }

    public function edit(Struktur $struktur)
    {
        return view('master.strukturs.edit', compact('struktur'));
    }

    public function update(Request $request, Struktur $struktur)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:strukturs,kode,' . $struktur->id,
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        $struktur->update($data);

        return redirect()->route('master.strukturs.index')
            ->with('success', 'Struktur berhasil diperbarui.');
    }

    public function destroy(Struktur $struktur)
    {
        abort_unless(request()->user()->isAdmin(), 403, 'Hanya administrator yang dapat menghapus data master.');

        $struktur->delete();

        return redirect()->route('master.strukturs.index')
            ->with('success', 'Struktur berhasil dihapus.');
    }
}
