<?php

namespace App\Http\Controllers;

use App\Actions\BuatDisposisi;
use App\Models\Disposisi;
use App\Models\RiwayatPerubahanData;
use App\Models\RiwayatStatus;
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
            'instruksi' => 'nullable|array',
            'instruksi.*' => 'string',
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
            instruksi: $data['instruksi'] ?? null,
            disposisiKepada: $data['disposisi_kepada'] ?? null,
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

    public function pindah(Request $request, Disposisi $disposisi)
    {
        $user = Auth::user();

        $isSekretarisAdmin = in_array($user->role->kode, ['sekretaris', 'admin']);
        $isPenerima = $disposisi->kepada_user_id === $user->id;

        if (!$isSekretarisAdmin && !$isPenerima) {
            abort(403, 'Anda tidak berhak memindahkan disposisi ini.');
        }

        $data = $request->validate([
            'pelaksana_id' => 'required|exists:users,id',
            'catatan' => 'nullable|string',
            'tanggal_deadline' => 'nullable|date',
        ]);

        if ((int) $data['pelaksana_id'] === (int) $disposisi->kepada_user_id) {
            return back()->with('error', 'Pelaksana baru sama dengan penerima disposisi saat ini.');
        }

        $surat = $disposisi->suratMasuk;

        $baru = Disposisi::create([
            'surat_masuk_id' => $surat->id,
            'disposisi_induk_id' => $disposisi->id,
            'dari_user_id' => $user->id,
            'kepada_user_id' => $data['pelaksana_id'],
            'jenis' => 'ke_pelaksana',
            'catatan' => $data['catatan'] ?? null,
            'tanggal_deadline' => $data['tanggal_deadline'] ?? null,
        ]);

        $kepadaNama = $baru->kepadaUser->name ?? '-';
        $dariNama = $disposisi->kepadaUser->name ?? 'Pimpinan';
        $catatanPindah = 'Disposisi dipindahkan dari ' . $dariNama . ' ke ' . $kepadaNama;

        if ($baru->followUps()->exists()) {
            RiwayatStatus::create([
                'surat_masuk_id' => $surat->id,
                'status_dari_id' => $surat->status_id,
                'status_ke_id' => $surat->status_id,
                'dilakukan_oleh_id' => $user->id,
                'catatan' => $catatanPindah,
                'disposisi_id' => $baru->id,
            ]);
        } else {
            $oldStatusId = $surat->status_id;
            $surat->update([
                'status_id' => statusId('assigned'),
                'completed_at' => null,
            ]);

            RiwayatStatus::create([
                'surat_masuk_id' => $surat->id,
                'status_dari_id' => $oldStatusId,
                'status_ke_id' => statusId('assigned'),
                'dilakukan_oleh_id' => $user->id,
                'catatan' => $catatanPindah,
                'disposisi_id' => $baru->id,
            ]);
        }

        return back()->with('success', 'Disposisi berhasil dipindahkan ke ' . $kepadaNama . '.');
    }

    public function update(Request $request, Disposisi $disposisi)
    {
        $user = Auth::user();

        $isSekretarisAdmin = in_array($user->role->kode, ['sekretaris', 'admin']);
        $isDari = $disposisi->dari_user_id === $user->id;

        if (!$isSekretarisAdmin && !$isDari) {
            abort(403, 'Anda tidak berhak mengubah disposisi ini.');
        }

        $data = $request->validate([
            'catatan' => 'nullable|string',
            'catatan_pimpinan' => 'nullable|string',
            'tanggal_deadline' => 'nullable|date',
            'instruksi' => 'nullable|array',
            'instruksi.*' => 'string',
        ]);

        $old = $disposisi->getAttributes();
        $disposisi->update($data);

        $labels = [
            'catatan' => 'Isi Disposisi',
            'catatan_pimpinan' => 'Catatan Pimpinan',
            'tanggal_deadline' => 'Batas Waktu',
            'instruksi' => 'Instruksi',
            'disposisi_kepada' => 'Disposisi kepada',
        ];

        $arrayFields = ['instruksi', 'disposisi_kepada'];

        $perubahan = [];
        foreach ($labels as $field => $label) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $modelVal = $old[$field] ?? null;
            $newVal = $data[$field];

            if ($field === 'tanggal_deadline') {
                $lama = $modelVal ? \Carbon\Carbon::parse($modelVal)->format('d/m/Y') : '-';
                $baru = $newVal ? \Carbon\Carbon::parse($newVal)->format('d/m/Y') : '-';
            } elseif (in_array($field, $arrayFields)) {
                $lama = $modelVal ? json_encode($modelVal) : '-';
                $baru = $newVal ? json_encode($newVal) : '-';
            } else {
                $lama = $modelVal ?? '-';
                $baru = $newVal ?? '-';
            }

            if ($lama === $baru) {
                continue;
            }

            $perubahan[$field] = [
                'label' => $label,
                'lama' => $lama,
                'baru' => $baru,
            ];
        }

        if (!empty($perubahan)) {
            RiwayatPerubahanData::create([
                'surat_masuk_id' => $disposisi->surat_masuk_id,
                'disposisi_id' => $disposisi->id,
                'dilakukan_oleh_id' => $user->id,
                'perubahan' => $perubahan,
                'catatan' => 'Disposisi diperbarui.',
            ]);
        }

        return back()->with('success', 'Disposisi berhasil diperbarui.');
    }

    public function destroy(Request $request, Disposisi $disposisi)
    {
        $user = Auth::user();

        $isSekretarisAdmin = in_array($user->role->kode, ['sekretaris', 'admin']);
        $isDari = $disposisi->dari_user_id === $user->id;

        if (!$isSekretarisAdmin && !$isDari) {
            abort(403, 'Anda tidak berhak menghapus disposisi ini.');
        }

        $surat = $disposisi->suratMasuk;
        $disposisi->delete();

        // Jika tidak ada disposisi lain, reset status surat
        if ($surat->disposisi()->count() === 0) {
            $oldStatusId = $surat->status_id;
            $surat->update(['status_id' => statusId('received')]);

            \App\Models\RiwayatStatus::create([
                'surat_masuk_id' => $surat->id,
                'status_dari_id' => $oldStatusId,
                'status_ke_id' => statusId('received'),
                'dilakukan_oleh_id' => $user->id,
                'catatan' => 'Disposisi dihapus, status dikembalikan ke Diterima.',
            ]);
        }

        return back()->with('success', 'Disposisi berhasil dihapus.');
    }
}
