<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Topik;
use Illuminate\Http\Request;

class TopikController extends Controller
{
    public function index()
    {
        $topik = Topik::latest()->paginate(request('per_page', 25));
        return view('master.topik.index', compact('topik'));
    }

    public function create()
    {
        return view('master.topik.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:50|unique:topik',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'color' => 'required|string|max:20',
        ]);

        Topik::create($data);

        return redirect()->route('master.topik.index')
            ->with('success', 'Topik berhasil dibuat.');
    }

    public function edit(Topik $topik)
    {
        return view('master.topik.edit', compact('topik'));
    }

    public function update(Request $request, Topik $topik)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:50|unique:topik,kode,' . $topik->id,
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'color' => 'required|string|max:20',
        ]);

        $topik->update($data);

        return redirect()->route('master.topik.index')
            ->with('success', 'Topik berhasil diperbarui.');
    }

    public function destroy(Topik $topik)
    {
        abort_unless(request()->user()->isAdmin(), 403, 'Hanya administrator yang dapat menghapus data master.');

        $topik->delete();

        return redirect()->route('master.topik.index')
            ->with('success', 'Topik berhasil dihapus.');
    }
}
