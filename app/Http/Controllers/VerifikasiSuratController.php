<?php

namespace App\Http\Controllers;

use App\Actions\VerifikasiSurat;
use App\Models\SuratMasuk;
use App\Services\SuratScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifikasiSuratController extends Controller
{
    public function index(SuratScopeService $scope)
    {
        $user = Auth::user();
        $surat = $scope->listSuratMasuk($user, ['status' => 'received']);

        return view('verifikasi-surat.index', compact('surat'));
    }

    public function verify(Request $request, VerifikasiSurat $action)
    {
        $data = $request->validate([
            'surat_id' => 'required|exists:surat_masuk,id',
            'catatan' => 'nullable|string',
        ]);

        $surat = SuratMasuk::findOrFail($data['surat_id']);

        if (!in_array(Auth::user()->role->kode, ['sekretaris', 'admin'])) {
            abort(403);
        }

        $action->execute($surat, Auth::user(), $data['catatan'] ?? null);

        return redirect()->route('verifikasi-surat.index')
            ->with('success', 'Surat berhasil diverifikasi.');
    }
}
