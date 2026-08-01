<?php

namespace App\Http\Controllers;

use App\Models\Klasifikasi;
use App\Models\StatusSurat;
use App\Models\SuratMasuk;
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
            $filters = $request->only(['search', 'status']);
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
            ]);

            return response()->json($data);
        }

        $statuses = StatusSurat::orderBy('urutan')->get();

        $baseQuery = SuratMasuk::query();
        $baseQuery = $scope->scopeForUser($baseQuery, $user);

        $total = (clone $baseQuery)->count();
        $perStatus = (clone $baseQuery)
            ->join('status_surat', 'surat_masuk.status_id', '=', 'status_surat.id')
            ->selectRaw('status_surat.kode, status_surat.label, count(*) as jumlah')
            ->groupBy('status_surat.kode', 'status_surat.label')
            ->pluck('jumlah', 'kode');
        $hariIni = (clone $baseQuery)->whereDate('created_at', today())->count();

        return view('penerimaan-surat.index', compact('statuses', 'total', 'perStatus', 'hariIni'));
    }

    public function create(AksesKlasifikasiService $akses)
    {
        $klasifikasi = $akses->getVisibleKlasifikasi(Auth::user());
        $organisasiList = \App\Models\OrganisasiPengirim::where('aktif', true)
            ->orderBy('nama')
            ->get();
        $pimpinanList = \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'pimpinan'))
            ->where('is_active', true)
            ->get(['id', 'name', 'title']);
        return view('penerimaan-surat.create', compact('klasifikasi', 'organisasiList', 'pimpinanList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_surat' => 'nullable|string|max:255',
            'pengirim' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'tanggal_surat' => 'nullable|date',
            'tanggal_diterima' => 'required|date',
            'klasifikasi_id' => 'required|exists:klasifikasi,id',
            'tujuan_pimpinan_id' => 'required|exists:users,id',
            'is_rahasia' => 'boolean',
            'notes' => 'nullable|string',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data['created_by'] = Auth::id();
        $data['status_id'] = statusId('received');
        $data['nomor_agenda'] = 'AG-' . now()->format('Ymd') . '-' . str_pad(SuratMasuk::max('id') + 1, 4, '0', STR_PAD_LEFT);

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

        return redirect()->route('penerimaan-surat.index')
            ->with('success', 'Surat masuk berhasil dicatat.');
    }

    public function edit(string $uuid, AksesKlasifikasiService $akses)
    {
        $surat = SuratMasuk::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $surat);

        if (!$akses->canAccess(Auth::user(), $surat->klasifikasi)) {
            abort(403, 'Anda tidak memiliki akses ke surat ini.');
        }

        $klasifikasi = $akses->getVisibleKlasifikasi(Auth::user());
        $organisasiList = \App\Models\OrganisasiPengirim::where('aktif', true)
            ->orderBy('nama')
            ->get();
        $pimpinanList = \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'pimpinan'))
            ->where('is_active', true)
            ->get(['id', 'name', 'title']);

        return view('penerimaan-surat.edit', compact('surat', 'klasifikasi', 'organisasiList', 'pimpinanList'));
    }

    public function update(Request $request, string $uuid, AksesKlasifikasiService $akses)
    {
        $surat = SuratMasuk::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $surat);

        if (!$akses->canAccess(Auth::user(), $surat->klasifikasi)) {
            abort(403, 'Anda tidak memiliki akses ke surat ini.');
        }

        $data = $request->validate([
            'nomor_surat' => 'nullable|string|max:255',
            'pengirim' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'tanggal_surat' => 'nullable|date',
            'tanggal_diterima' => 'required|date',
            'klasifikasi_id' => 'required|exists:klasifikasi,id',
            'tujuan_pimpinan_id' => 'required|exists:users,id',
            'is_rahasia' => 'boolean',
            'notes' => 'nullable|string',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data['is_rahasia'] = $request->boolean('is_rahasia');

        if (!empty($data['pengirim'])) {
            $organisasi = \App\Models\OrganisasiPengirim::firstOrCreate(
                ['nama' => trim($data['pengirim'])],
                ['alamat' => null, 'kontak' => null, 'aktif' => true]
            );
            $data['pengirim_id'] = $organisasi->id;
        }

        $files = $request->file('lampiran');
        if ($files && is_array($files)) {
            $lampiran = is_array($surat->lampiran) ? $surat->lampiran : [];
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
                    $lampiran[] = [
                        'original_name' => $originalName,
                        'stored_path' => 'surat-masuk/' . $filename,
                        'mime_type' => $mimeType,
                        'file_size' => $fileSize,
                    ];
                } catch (\Throwable $e) {
                    Log::error('SM lampiran update failed', ['error' => $e->getMessage()]);
                }
            }
            $data['lampiran'] = $lampiran;
        }

        $perubahan = $this->diffPerubahan($surat, $data);

        $surat->update($data);

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
            'is_rahasia' => 'Sifat Surat',
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

            if ($field === 'is_rahasia') {
                $nilaiLama = $nilaiLama ? 'Rahasia' : 'Biasa';
                $nilaiBaru = $nilaiBaru ? 'Rahasia' : 'Biasa';
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
        $surat = SuratMasuk::with(['klasifikasi', 'status', 'pembuat', 'verifikator', 'riwayatStatus.statusKe', 'riwayatStatus.dilakukanOleh.role', 'riwayatStatus.atasNamaPimpinan.role', 'riwayatStatus.disposisi.kepadaUser.direktorat', 'riwayatStatus.disposisi.kepadaUser.divisi', 'riwayatStatus.disposisi.kepadaUser.departemen', 'riwayatPerubahanData.dilakukanOleh', 'riwayatPerubahanData.disposisi.kepadaUser', 'disposisi.dariUser', 'disposisi.kepadaUser.direktorat', 'disposisi.kepadaUser.divisi', 'disposisi.kepadaUser.departemen', 'disposisi.lampiran', 'disposisi.followUps.user', 'aksesManual.user', 'aksesManual.diberikanOleh'])
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
        $surat = SuratMasuk::with(['klasifikasi', 'status', 'disposisi.dariUser', 'disposisi.kepadaUser.direktorat', 'disposisi.kepadaUser.divisi', 'disposisi.kepadaUser.departemen', 'disposisi.atasNamaPimpinan', 'disposisi.followUps.user'])
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
}
