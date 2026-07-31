<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\OrganisasiPengirim;
use Illuminate\Http\Request;

class OrganisasiPengirimController extends Controller
{
    public function index(Request $request)
    {
        $query = OrganisasiPengirim::withCount('suratMasuk');

        if ($q = $request->get('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('nama', 'like', "%{$q}%")
                    ->orWhere('alamat', 'like', "%{$q}%")
                    ->orWhere('kontak', 'like', "%{$q}%");
            });
        }

        if (($aktif = $request->get('aktif')) !== null && $aktif !== '') {
            $query->where('aktif', $aktif);
        }

        $organisasi = $query->latest()->paginate($request->get('per_page', 25));

        return view('master.organisasi-pengirims.index', compact('organisasi'));
    }

    public function create()
    {
        return view('master.organisasi-pengirims.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'kontak' => 'nullable|string|max:255',
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        OrganisasiPengirim::create($data);

        return redirect()->route('master.organisasi-pengirims.index')
            ->with('success', 'Organisasi pengirim berhasil dibuat.');
    }

    public function edit(OrganisasiPengirim $organisasiPengirim)
    {
        return view('master.organisasi-pengirims.edit', compact('organisasiPengirim'));
    }

    public function update(Request $request, OrganisasiPengirim $organisasiPengirim)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'kontak' => 'nullable|string|max:255',
            'aktif' => 'boolean',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        $organisasiPengirim->update($data);

        return redirect()->route('master.organisasi-pengirims.index')
            ->with('success', 'Organisasi pengirim berhasil diperbarui.');
    }

    public function destroy(OrganisasiPengirim $organisasiPengirim)
    {
        if ($organisasiPengirim->suratMasuk()->exists()) {
            return back()->with('error', 'Organisasi masih digunakan oleh surat masuk.');
        }

        $organisasiPengirim->delete();

        return redirect()->route('master.organisasi-pengirims.index')
            ->with('success', 'Organisasi pengirim berhasil dihapus.');
    }
}
