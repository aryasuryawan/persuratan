<?php

namespace App\Http\Controllers;

use App\Models\Klasifikasi;
use App\Models\StatusSurat;
use App\Models\SuratMasuk;
use App\Models\Topik;
use App\Services\AksesKlasifikasiService;
use App\Services\SuratScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PenerimaanSuratController extends Controller
{
    public function index(Request $request, SuratScopeService $scope)
    {
        $user = Auth::user();

        if ($request->ajax()) {
            $filters = $request->only(['search', 'status', 'klasifikasi_id', 'topik_id']);
            $surat = $scope->listSuratMasuk($user, $filters);

            $data = $surat->through(fn($s) => [
                'uuid' => $s->uuid,
                'nomor_agenda' => $s->nomor_agenda,
                'pengirim' => $s->pengirim,
                'organisasi_pengirim' => $s->organisasiPengirim?->nama,
                'perihal' => $s->perihal,
                'tanggal_diterima' => $s->tanggal_diterima->format('d/m/Y'),
                'status_kode' => $s->status->kode,
                'status_label' => $s->status->label,
                'klasifikasi' => $s->klasifikasi->nama,
                'klasifikasi_color' => $s->klasifikasi->color,
                'topiks' => $s->topiks->map(fn($t) => ['nama' => $t->nama, 'color' => $t->color])->toArray(),
            ]);

            return response()->json($data);
        }

        $statuses = StatusSurat::orderBy('urutan')->get();
        $klasifikasi = Klasifikasi::orderBy('nama')->get();
        $topiks = Topik::orderBy('nama')->get();

        $baseQuery = SuratMasuk::query();
        $baseQuery = $scope->scopeForUser($baseQuery, $user);

        $total = (clone $baseQuery)->count();
        $perStatus = (clone $baseQuery)
            ->join('status_surat', 'surat_masuk.status_id', '=', 'status_surat.id')
            ->selectRaw('status_surat.kode, status_surat.label, count(*) as jumlah')
            ->groupBy('status_surat.kode', 'status_surat.label')
            ->pluck('jumlah', 'kode');
        $hariIni = (clone $baseQuery)->whereDate('created_at', today())->count();

        return view('penerimaan-surat.index', compact('statuses', 'total', 'perStatus', 'hariIni', 'klasifikasi', 'topiks'));
    }

    public function create(AksesKlasifikasiService $akses)
    {
        $klasifikasi = $akses->getVisibleKlasifikasi(Auth::user());
        $topiks = Topik::orderBy('nama')->get();
        $organisasiList = \App\Models\OrganisasiPengirim::where('aktif', true)
            ->orderBy('nama')
            ->get();
        $pimpinanList = \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'pimpinan'))
            ->where('is_active', true)
            ->get(['id', 'name', 'title']);
        return view('penerimaan-surat.create', compact('klasifikasi', 'topiks', 'organisasiList', 'pimpinanList'));
    }

    public function store(Request $request)
    {
        // Parse topik_ids from JSON string (Alpine.js hidden input)
        $rawTopikIds = $request->input('topik_ids');
        if (is_string($rawTopikIds)) {
            $decoded = json_decode($rawTopikIds, true);
            $request->merge(['topik_ids' => is_array($decoded) ? $decoded : []]);
        }

        $data = $request->validate([
            'nomor_surat' => 'nullable|string|max:255',
            'pengirim' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'tanggal_surat' => 'nullable|date',
            'tanggal_diterima' => 'required|date',
            'klasifikasi_id' => 'required|exists:klasifikasi,id',
            'tujuan_pimpinan_id' => 'required|exists:users,id',
            'topik_ids' => 'nullable|array',
            'topik_ids.*' => 'exists:topik,id',
            'notes' => 'nullable|string',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data['created_by'] = Auth::id();
        $data['status_id'] = statusId('received');
        $data['nomor_agenda'] = 'AG-' . now()->format('Ymd') . '-' . str_pad(SuratMasuk::max('id') + 1, 4, '0', STR_PAD_LEFT);

        $topikIds = $data['topik_ids'] ?? [];
        unset($data['topik_ids']);

        $lampiran = [];
        $files = $request->file('lampiran');

        if ($files && is_array($files)) {
            foreach ($files as $file) {
                try {
                    if (!$file instanceof UploadedFile || !$file->isValid()) {
                        Log::warning('SM lampiran skip: invalid file');
                        continue;
                    }
                    $originalName = $file->getClientOriginalName();
                    $mimeType = $file->getMimeType();
                    $fileSize = $file->getSize();
                    $ext = $file->getClientOriginalExtension() ?: 'bin';
                    $destDir = storage_path('app/public/surat-masuk');
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    $filename = Str::random(40) . '.' . $ext;
                    $file->move($destDir, $filename);
                    $path = 'surat-masuk/' . $filename;
                    $lampiran[] = [
                        'original_name' => $originalName,
                        'stored_path' => $path,
                        'mime_type' => $mimeType,
                        'file_size' => $fileSize,
                    ];
                    Log::info('SM lampiran stored', ['path' => $path]);
                } catch (\Throwable $e) {
                    Log::error('SM lampiran upload failed', ['error' => $e->getMessage()]);
                }
            }
        }

        $data['lampiran'] = !empty($lampiran) ? $lampiran : null;

        if (!empty($data['pengirim'])) {
            $organisasi = \App\Models\OrganisasiPengirim::firstOrCreate(
                ['nama' => trim($data['pengirim'])],
                ['alamat' => null, 'kontak' => null, 'aktif' => true]
            );
            $data['pengirim_id'] = $organisasi->id;
        }

        SuratMasuk::create($data);

        $surat = SuratMasuk::latest()->first();
        $surat->topiks()->sync($topikIds);

        return redirect()->route('penerimaan-surat.index')
            ->with('success', 'Surat masuk berhasil dicatat.');
    }

    public function edit(string $uuid, AksesKlasifikasiService $akses)
    {
        $surat = SuratMasuk::with('topiks')->where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $surat);

        if (!$akses->canAccess(Auth::user(), $surat->klasifikasi)) {
            abort(403, 'Anda tidak memiliki akses ke surat ini.');
        }

        $klasifikasi = $akses->getVisibleKlasifikasi(Auth::user());
        $topiks = Topik::orderBy('nama')->get();
        $selectedTopiks = $surat->topiks->pluck('id')->toArray();
        $organisasiList = \App\Models\OrganisasiPengirim::where('aktif', true)
            ->orderBy('nama')
            ->get();
        $pimpinanList = \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'pimpinan'))
            ->where('is_active', true)
            ->get(['id', 'name', 'title']);

        return view('penerimaan-surat.edit', compact('surat', 'klasifikasi', 'topiks', 'selectedTopiks', 'organisasiList', 'pimpinanList'));
    }

    public function update(Request $request, string $uuid, AksesKlasifikasiService $akses)
    {
        $surat = SuratMasuk::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $surat);

        if (!$akses->canAccess(Auth::user(), $surat->klasifikasi)) {
            abort(403, 'Anda tidak memiliki akses ke surat ini.');
        }

        // Parse topik_ids from JSON string (Alpine.js hidden input)
        $rawTopikIds = $request->input('topik_ids');
        if (is_string($rawTopikIds)) {
            $decoded = json_decode($rawTopikIds, true);
            $request->merge(['topik_ids' => is_array($decoded) ? $decoded : []]);
        }

        $data = $request->validate([
            'nomor_surat' => 'nullable|string|max:255',
            'pengirim' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'tanggal_surat' => 'nullable|date',
            'tanggal_diterima' => 'required|date',
            'klasifikasi_id' => 'required|exists:klasifikasi,id',
            'tujuan_pimpinan_id' => 'required|exists:users,id',
            'topik_ids' => 'nullable|array',
            'topik_ids.*' => 'exists:topik,id',
            'notes' => 'nullable|string',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'hapus_lampiran' => 'nullable|array',
            'hapus_lampiran.*' => 'string',
        ]);

        $topikIds = $data['topik_ids'] ?? [];
        unset($data['topik_ids']);

        if (!empty($data['pengirim'])) {
            $organisasi = \App\Models\OrganisasiPengirim::firstOrCreate(
                ['nama' => trim($data['pengirim'])],
                ['alamat' => null, 'kontak' => null, 'aktif' => true]
            );
            $data['pengirim_id'] = $organisasi->id;
        }

        // Upload new lampiran
        $lampiranBaru = [];
        $files = $request->file('lampiran');
        if ($files && is_array($files)) {
            foreach ($files as $file) {
                try {
                    if (!$file instanceof UploadedFile || !$file->isValid()) {
                        continue;
                    }
                    $originalName = $file->getClientOriginalName();
                    $mimeType = $file->getMimeType();
                    $fileSize = $file->getSize();
                    $ext = $file->getClientOriginalExtension() ?: 'bin';
                    $destDir = storage_path('app/public/surat-masuk');
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    $filename = Str::random(40) . '.' . $ext;
                    $file->move($destDir, $filename);
                    $lampiranBaru[] = [
                        'original_name' => $originalName,
                        'stored_path' => 'surat-masuk/' . $filename,
                        'mime_type' => $mimeType,
                        'file_size' => $fileSize,
                    ];
                } catch (\Throwable $e) {
                    Log::error('SM lampiran update failed', ['error' => $e->getMessage()]);
                }
            }
        }

        // Merge lampiran: keep existing (minus yg dihapus) + baru
        $existing = $surat->lampiran ?? [];
        $hapus = $request->get('hapus_lampiran', []);
        $keep = array_filter($existing, fn($f) => !in_array($f['stored_path'] ?? '', $hapus));
        $data['lampiran'] = !empty($lampiranBaru) || !empty($keep)
            ? array_merge(array_values($keep), $lampiranBaru)
            : null;

        $perubahan = $this->diffPerubahan($surat, $data);

        $surat->update($data);
        $surat->topiks()->sync($topikIds);

        if (!empty($perubahan)) {
            \App\Models\RiwayatPerubahanData::create([
                'surat_masuk_id' => $surat->id,
                'dilakukan_oleh_id' => Auth::id(),
                'perubahan' => $perubahan,
                'catatan' => 'Data surat diperbarui.',
            ]);
        }

        return redirect()->route('penerimaan-surat.show', $surat->uuid)
            ->with('success', 'Data surat masuk berhasil diperbarui.');
    }

    protected function diffPerubahan(SuratMasuk $surat, array $data): array
    {
        $labels = [
            'nomor_surat' => 'No. Surat',
            'pengirim' => 'Pengirim',
            'pengirim_id' => 'Organisasi Pengirim',
            'perihal' => 'Perihal',
            'tanggal_surat' => 'Tanggal Surat',
            'tanggal_diterima' => 'Tanggal Diterima',
            'klasifikasi_id' => 'Klasifikasi',
            'notes' => 'Catatan',
            'lampiran' => 'Lampiran',
        ];

        $old = $surat->getOriginal();
        $perubahan = [];

        foreach ($labels as $field => $label) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $nilaiLama = $old[$field] ?? null;
            $nilaiBaru = $data[$field] ?? null;

            if ($field === 'lampiran') {
                $jumlahLama = is_array($nilaiLama) ? count($nilaiLama) : 0;
                $jumlahBaru = is_array($nilaiBaru) ? count($nilaiBaru) : 0;
                if ($jumlahLama !== $jumlahBaru) {
                    $perubahan[$field] = [
                        'label' => $label,
                        'lama' => $jumlahLama . ' file',
                        'baru' => $jumlahBaru . ' file',
                    ];
                }
                continue;
            }

            if ((string) $nilaiLama === (string) $nilaiBaru) {
                continue;
            }

            if ($field === 'tanggal_surat' || $field === 'tanggal_diterima') {
                $nilaiLama = $nilaiLama ? date('d/m/Y', strtotime($nilaiLama)) : '-';
                $nilaiBaru = $nilaiBaru ? date('d/m/Y', strtotime($nilaiBaru)) : '-';
            }

            if ($field === 'pengirim_id') {
                $nilaiLama = $nilaiLama ? (\App\Models\OrganisasiPengirim::find($nilaiLama)?->nama ?? '-') : '-';
                $nilaiBaru = $nilaiBaru ? (\App\Models\OrganisasiPengirim::find($nilaiBaru)?->nama ?? '-') : '-';
            }

            if ($field === 'klasifikasi_id') {
                $nilaiLama = $nilaiLama ? (\App\Models\Klasifikasi::find($nilaiLama)?->nama ?? '-') : '-';
                $nilaiBaru = $nilaiBaru ? (\App\Models\Klasifikasi::find($nilaiBaru)?->nama ?? '-') : '-';
            }

            $perubahan[$field] = [
                'label' => $label,
                'lama' => $nilaiLama,
                'baru' => $nilaiBaru,
            ];
        }

        return $perubahan;
    }

    public function show(string $uuid, AksesKlasifikasiService $akses)
    {
        $surat = SuratMasuk::with(['klasifikasi', 'topiks', 'status', 'pembuat', 'verifikator', 'riwayatStatus.statusKe', 'riwayatStatus.dilakukanOleh.role', 'riwayatStatus.atasNamaPimpinan.role', 'riwayatStatus.disposisi.kepadaUser.direktorat', 'riwayatStatus.disposisi.kepadaUser.divisi', 'riwayatStatus.disposisi.kepadaUser.departemen', 'riwayatPerubahanData.dilakukanOleh', 'riwayatPerubahanData.disposisi.kepadaUser', 'disposisi.dariUser', 'disposisi.kepadaUser.direktorat', 'disposisi.kepadaUser.divisi', 'disposisi.kepadaUser.departemen', 'disposisi.lampiran', 'disposisi.followUps.user', 'aksesManual.user', 'aksesManual.diberikanOleh'])
            ->where('uuid', $uuid)->firstOrFail();

        $this->authorize('view', $surat);

        $canManageAkses = $surat->klasifikasi->kode === 'restricted' && (
            Auth::user()->hasRole('admin') ||
            $surat->disposisi()->where('atas_nama_pimpinan_id', Auth::id())->exists() ||
            (Auth::user()->hasRole('sekretaris') && $surat->disposisi()->whereNotNull('atas_nama_pimpinan_id')->where(function ($q) {
                $q->whereExists(function ($sub) {
                    $sub->selectRaw(1)
                        ->from('sekretaris_pimpinan as sp')
                        ->whereRaw('sp.pimpinan_id = disposisi.atas_nama_pimpinan_id')
                        ->where('sp.sekretaris_id', Auth::id())
                        ->where('sp.is_active', true);
                });
            })->exists())
        );

        return view('penerimaan-surat.show', compact('surat', 'canManageAkses'))
            ->with('emailLogs', \App\Models\EmailNotificationLog::where('type', 'disposisi')
                ->whereIn('reference_id', $surat->disposisi()->pluck('id'))
                ->latest('created_at')
                ->get());
    }

    public function cetakDisposisi(string $uuid, AksesKlasifikasiService $akses)
    {
        $surat = SuratMasuk::with(['klasifikasi', 'topiks', 'status', 'disposisi.dariUser', 'disposisi.kepadaUser.direktorat', 'disposisi.kepadaUser.divisi', 'disposisi.kepadaUser.departemen', 'disposisi.atasNamaPimpinan', 'disposisi.followUps.user'])
            ->where('uuid', $uuid)->firstOrFail();

        if (!$akses->canAccess(Auth::user(), $surat->klasifikasi)) {
            abort(403, 'Anda tidak memiliki akses ke surat ini.');
        }

        return view('penerimaan-surat.cetak-disposisi', compact('surat'));
    }

    public function listAkses(string $uuid)
    {
        $surat = SuratMasuk::where('uuid', $uuid)->firstOrFail();
        $this->authorize('view', $surat);

        $akses = $surat->aksesManual()->with(['user', 'diberikanOleh'])->get();

        return response()->json($akses->map(function ($a) {
            return [
                'user_id' => $a->user_id,
                'user_name' => $a->user->name,
                'user_email' => $a->user->email,
                'diberikan_oleh_name' => $a->diberikanOleh->name,
                'created_at_formatted' => $a->created_at?->format('d/m/Y H:i'),
            ];
        }));
    }

    public function assignAkses(Request $request, string $uuid)
    {
        $surat = SuratMasuk::where('uuid', $uuid)->firstOrFail();

        $this->authorize('assignAkses', $surat);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $surat->aksesManual()->firstOrCreate(
            ['user_id' => $data['user_id']],
            ['level' => 'view', 'diberikan_oleh_id' => Auth::id()],
        );

        return response()->json(['message' => 'Akses berhasil diberikan.']);
    }

    public function removeAkses(string $uuid, string $userId)
    {
        $surat = SuratMasuk::where('uuid', $uuid)->firstOrFail();

        $this->authorize('assignAkses', $surat);

        $surat->aksesManual()->where('user_id', $userId)->delete();

        return response()->json(['message' => 'Akses berhasil dicabut.']);
    }

    public function trash(Request $request)
    {
        $this->authorize('viewAny', SuratMasuk::class);

        $query = SuratMasuk::onlyTrashed();

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_agenda', 'like', "%{$q}%")
                    ->orWhere('nomor_surat', 'like', "%{$q}%")
                    ->orWhere('pengirim', 'like', "%{$q}%")
                    ->orWhere('perihal', 'like', "%{$q}%");
            });
        }

        $items = $query->latest('deleted_at')->paginate($request->get('per_page', 25));

        return view('penerimaan-surat.trash', compact('items'));
    }

    public function destroy(string $uuid)
    {
        $surat = SuratMasuk::where('uuid', $uuid)->firstOrFail();

        // Cleanup JSON lampiran files from disk
        if ($surat->lampiran) {
            foreach ($surat->lampiran as $file) {
                $path = storage_path('app/public/' . ($file['stored_path'] ?? ''));
                if ($path && file_exists($path)) {
                    @unlink($path);
                }
            }
        }

        $surat->delete();

        return redirect()->route('penerimaan-surat.index')
            ->with('success', 'Surat masuk berhasil dihapus.');
    }

    public function restore(string $uuid)
    {
        $surat = SuratMasuk::withTrashed()->where('uuid', $uuid)->firstOrFail();
        $surat->restore();

        return redirect()->route('penerimaan-surat.show', $uuid)
            ->with('success', 'Surat masuk berhasil dipulihkan.');
    }

    public function forceDelete(string $uuid)
    {
        $surat = SuratMasuk::withTrashed()->where('uuid', $uuid)->firstOrFail();

        // Cleanup JSON lampiran files from disk
        if ($surat->lampiran) {
            foreach ($surat->lampiran as $file) {
                $path = storage_path('app/public/' . ($file['stored_path'] ?? ''));
                if ($path && file_exists($path)) {
                    @unlink($path);
                }
            }
        }

        $surat->forceDelete();

        return redirect()->route('penerimaan-surat.index')
            ->with('success', 'Surat masuk berhasil dihapus permanen.');
    }
}
