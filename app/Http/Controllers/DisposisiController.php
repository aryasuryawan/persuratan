<?php

namespace App\Http\Controllers;

use App\Actions\BuatDisposisi;
use App\Models\Disposisi;
use App\Models\RiwayatPerubahanData;
use App\Models\RiwayatStatus;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Services\EmailService;
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
            'catatan' => 'nullable|string',
            'catatan_pimpinan' => 'nullable|string',
            'tanggal_deadline' => 'nullable|date',
            'instruksi' => 'nullable|array',
            'instruksi.*' => 'string',
            'disposisi_kepada' => 'nullable|array',
            'disposisi_kepada.*' => 'string',
            'disposisi_kepada_free' => 'nullable|array',
            'disposisi_kepada_free.*' => 'nullable|string',
            'disposisi_kepada_free_email' => 'nullable|array',
            'disposisi_kepada_free_email.*' => 'nullable|email',
            'cc' => 'nullable|array',
            'cc.*' => 'nullable|email',
            'cc_free' => 'nullable|string',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data['cc'] = $this->resolveCcEmails($data);

        // Merge freetext recipients (name + optional email) into disposisi_kepada.
        // Nama+email yang belum terdaftar otomatis dibuat sebagai user baru.
        $data['disposisi_kepada'] = $this->resolveDisposisiKepada($data);

        // Validasi: ke_pelaksana butuh minimal 1 user di disposisi_kepada
        // Kecuali jika disisi dengan nama/jabatan bebas (disposisi_kepada_free)
        if ($data['jenis'] === 'ke_pelaksana' && empty($data['disposisi_kepada'])) {
            $hasValidFreeText = false;
            foreach ($data['disposisi_kepada_free'] ?? [] as $i => $name) {
                if ($name && trim($name) !== '') {
                    $email = trim($data['disposisi_kepada_free_email'][$i] ?? '');
                    if ($email) {
                        $hasValidFreeText = true;
                        break;
                    }
                }
            }
            if (!$hasValidFreeText) {
                return back()->withErrors(['disposisi_kepada' => 'Minimal pilih 1 pelaksana untuk jenis "Ke Pelaksana".'])->withInput();
            }
        }

        // Tentukan pelaksana (kepada_user_id) dari user pertama di disposisi_kepada yang ada di tabel users
        $pelaksanaUser = null;
        if ($data['jenis'] === 'ke_pelaksana' && !empty($data['disposisi_kepada'])) {
            $userNames = User::where('is_active', true)->pluck('id', 'name')->toArray();
            foreach ($data['disposisi_kepada'] as $item) {
                if (isset($userNames[$item])) {
                    $pelaksanaUser = User::find($userNames[$item]);
                    break;
                }
            }
        }

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
            kepadaUser: $pelaksanaUser,
            catatan: $data['catatan'] ?? null,
            catatanPimpinan: $data['catatan_pimpinan'] ?? null,
            tanggalDeadline: $data['tanggal_deadline'] ?? null,
            instruksi: $data['instruksi'] ?? null,
            disposisiKepada: $data['disposisi_kepada'] ?? null,
            lampiran: $request->file('lampiran', []),
            ccEmails: $data['cc'],
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

    private function resolveDisposisiKepada(array $data): array
    {
        $names = array_values(array_filter(
            $data['disposisi_kepada'] ?? [],
            fn($n) => is_string($n) && trim($n) !== ''
        ));

        foreach ($data['disposisi_kepada_free'] ?? [] as $i => $name) {
            if ($name === null || trim($name) === '') {
                continue;
            }
            $name = trim($name);
            $email = trim($data['disposisi_kepada_free_email'][$i] ?? '');

            if ($email === '') {
                $names[] = $name;
                continue;
            }

            $user = User::where('name', $name)->where('is_active', true)->first()
                ?? User::where('email', $email)->first();

            if (!$user) {
                if (empty($email)) {
                    continue;
                }
                $roleId = \App\Models\Role::where('kode', 'unit_lain')->value('id')
                    ?? \App\Models\Role::first()?->id;
                if ($roleId) {
                    $user = User::create([
                        'role_id' => $roleId,
                        'name' => $name,
                        'email' => $email,
                        'password' => 'Password99',
                        'is_active' => true,
                    ]);
                }
            }

            $names[] = $user?->name ?? $name;
        }

        return array_values(array_unique($names));
    }

    private function resolveCcEmails(array $data): array
    {
        $emails = array_filter($data['cc'] ?? [], fn($e) => is_string($e) && trim($e) !== '');

        $free = trim($data['cc_free'] ?? '');
        if ($free !== '') {
            $parts = preg_split('/[,\s]+/', $free) ?: [];
            foreach ($parts as $part) {
                if (trim($part) !== '') {
                    $emails[] = trim($part);
                }
            }
        }

        $valid = [];
        foreach ($emails as $email) {
            $email = strtolower(trim($email));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $valid[] = $email;
            }
        }

        return array_values(array_unique($valid));
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

        $emailInstruksi = $baru->instruksi ? implode(', ', $baru->instruksi) : '-';
        $emailCatatan = nl2br(e($data['catatan'] ?? '-'));
        $emailDeadline = $baru->tanggal_deadline ? $baru->tanggal_deadline->format('d/m/Y') : '-';
        $emailPerihal = $surat->perihal;
        $emailAgenda = $surat->nomor_agenda;
        $emailUrl = route('penerimaan-surat.show', $surat->uuid);

        if ($baru->kepadaUser?->email) {
            app(EmailService::class)->send(
                $baru->kepadaUser->email,
                "Disposisi Dipindahkan: {$emailAgenda} - {$emailPerihal}",
                "Disposisi surat <strong>{$emailPerihal}</strong> (No. Agenda: {$emailAgenda}) dipindahkan dari <strong>{$dariNama}</strong> kepada Anda.<br><br>" .
                "Instruksi: {$emailInstruksi}<br>" .
                "Catatan: {$emailCatatan}<br>" .
                "Batas Waktu: {$emailDeadline}<br><br>" .
                "<a href=\"{$emailUrl}\" style=\"display:inline-block;background:#2563eb;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;\">Lihat Detail Surat</a>",
                [
                    'type' => 'disposisi',
                    'reference_id' => $baru->id,
                    'recipient_type' => 'user',
                    'recipient_user_id' => $baru->kepada_user_id,
                    'recipient_name' => $baru->kepadaUser->name,
                ]
            );
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
            'disposisi_kepada' => 'nullable|array',
            'disposisi_kepada.*' => 'string',
            'disposisi_kepada_free' => 'nullable|array',
            'disposisi_kepada_free.*' => 'nullable|string',
            'disposisi_kepada_free_email' => 'nullable|array',
            'disposisi_kepada_free_email.*' => 'nullable|email',
        ]);

        // Merge freetext recipients (name + optional email) into disposisi_kepada.
        // Nama+email yang belum terdaftar otomatis dibuat sebagai user baru.
        $data['disposisi_kepada'] = $this->resolveDisposisiKepada($data);

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

        return redirect()->back()->with('success', 'Disposisi berhasil diperbarui.')->withFragment('disposisi-edit-' . $disposisi->id);
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
