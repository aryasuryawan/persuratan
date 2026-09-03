<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Klasifikasi;
use Illuminate\Http\Request;

class KlasifikasiController extends Controller
{
    public function index()
    {
        $klasifikasi = Klasifikasi::withCount('suratMasuk')->latest()->paginate(request('per_page', 25));
        return view('master.klasifikasi.index', compact('klasifikasi'));
    }

    public function create()
    {
        return view('master.klasifikasi.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:50|unique:klasifikasi',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'color' => 'required|string|max:20',
            'is_rahasia' => 'boolean',
        ]);

        Klasifikasi::create($data);

        return redirect()->route('master.klasifikasi.index')
            ->with('success', 'Klasifikasi berhasil dibuat.');
    }

    public function edit(Klasifikasi $klasifikasi)
    {
        return view('master.klasifikasi.edit', compact('klasifikasi'));
    }

    public function update(Request $request, Klasifikasi $klasifikasi)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:50|unique:klasifikasi,kode,' . $klasifikasi->id,
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'color' => 'required|string|max:20',
            'is_rahasia' => 'boolean',
        ]);

        $klasifikasi->update($data);

        return redirect()->route('master.klasifikasi.index')
            ->with('success', 'Klasifikasi berhasil diperbarui.');
    }

    public function destroy(Klasifikasi $klasifikasi)
    {
        abort_unless(request()->user()->isAdmin(), 403, 'Hanya administrator yang dapat menghapus data master.');

        if ($klasifikasi->suratMasuk()->exists()) {
            return back()->with('error', 'Klasifikasi masih digunakan oleh surat.');
        }

        $klasifikasi->delete();

        return redirect()->route('master.klasifikasi.index')
            ->with('success', 'Klasifikasi berhasil dihapus.');
    }
}
