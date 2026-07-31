<?php

namespace App\Http\Controllers;

use App\Actions\BuatDisposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Services\SuratScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisposisiController extends Controller
{
    public function index(SuratScopeService $scope)
    {
        $user = Auth::user();
        $query = SuratMasuk::with(['klasifikasi', 'status', 'pembuat']);
        $query = $scope->scopeForUser($query, $user);
        $query->whereHas('status', fn($q) => $q->whereIn('kode', ['received', 'verified', 'forwarded_to_leadership']));
        $surat = $query->latest('tanggal_diterima')->paginate(request('per_page', 25));

        return view('disposisi.index', compact('surat'));
    }

    public function store(Request $request, BuatDisposisi $action)
    {
        // Prevent duplicate submission via one-time token
        $token = $request->input('_submit_token');
        $tokens = session('submit_tokens', []);
        if ($token) {
            if (!in_array($token, $tokens)) {
                $surat = SuratMasuk::findOrFail($request->input('surat_masuk_id'));
                return redirect()->route('penerimaan-surat.show', $surat->uuid)
                    ->with('error', 'Form sudah dikirim. Tidak boleh dikirim ulang.');
            }
            session()->put('submit_tokens', array_values(array_diff($tokens, [$token])));
        }

        $data = $request->validate([
            'surat_masuk_id' => 'required|exists:surat_masuk,id',
            'jenis' => 'required|in:ke_pelaksana,langsung_pimpinan',
            'atas_nama_pimpinan_id' => 'nullable|exists:users,id',
            'pelaksana_id' => 'nullable|exists:users,id',
            'catatan' => 'nullable|string',
            'catatan_pimpinan' => 'nullable|string',
            'tanggal_deadline' => 'nullable|date',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $surat = SuratMasuk::with('status')->findOrFail($data['surat_masuk_id']);
        $user = Auth::user();

        if (in_array($surat->status->kode, ['assigned', 'handled_by_leadership', 'in_progress', 'completed', 'archived'])) {
            return redirect()->route('penerimaan-surat.show', $surat->uuid)
                ->with('error', 'Surat sudah didisposisikan. Tidak bisa disposisi ulang.');
        }

        $action->execute(
            surat: $surat,
            dariUser: $user,
            jenis: $data['jenis'],
            atasNamaPimpinan: ($data['atas_nama_pimpinan_id'] ?? null) ? User::find($data['atas_nama_pimpinan_id']) : null,
            kepadaUser: ($data['pelaksana_id'] ?? null) ? User::find($data['pelaksana_id']) : null,
            catatan: $data['catatan'] ?? null,
            catatanPimpinan: $data['catatan_pimpinan'] ?? null,
            tanggalDeadline: $data['tanggal_deadline'] ?? null,
            lampiran: $request->file('lampiran', []),
        );

        return redirect()->route('penerimaan-surat.show', $surat->uuid)
            ->with('success', 'Disposisi berhasil dibuat.');
    }

    public function pelaksanaOptions()
    {
        $users = User::whereHas('role', fn($q) => $q->where('kode', 'unit_lain'))
            ->where('is_active', true)
            ->get(['id', 'name']);

        return response()->json($users);
    }
}
