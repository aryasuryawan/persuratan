<?php

namespace App\Http\Controllers;

use App\Models\Klasifikasi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Services\AksesKlasifikasiService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', SuratKeluar::class);

        $query = SuratKeluar::with(['klasifikasi', 'pembuat', 'kodeSurat']);

        if ($search = $request->get('q')) {
            $query->whereAny(['nomor_surat', 'perihal', 'tujuan_surat'], 'like', "%{$search}%");
        }
        if ($klasifikasiId = $request->get('klasifikasi_id')) {
            $query->where('klasifikasi_id', $klasifikasiId);
        }
        if ($kodeSuratId = $request->get('kode_surat_id')) {
            $query->where('kode_surat_id', $kodeSuratId);
        }
        if ($tglAwal = $request->get('tgl_awal')) {
            $query->whereDate('tanggal_surat', '>=', $tglAwal);
        }
        if ($tglAkhir = $request->get('tgl_akhir')) {
            $query->whereDate('tanggal_surat', '<=', $tglAkhir);
        }

        $surat = $query->latest()->paginate($request->get('per_page', 25));
        $klasifikasi = Klasifikasi::all();
        $kodeSurats = \App\Models\KodeSurat::where('aktif', true)->get();
        return view('surat-keluar.index', compact('surat', 'klasifikasi', 'kodeSurats'));
    }

    public function create(AksesKlasifikasiService $akses)
    {
        $this->authorize('create', SuratKeluar::class);

        $klasifikasi = $akses->getVisibleKlasifikasi(Auth::user());
        $suratMasuk = SuratMasuk::where('status_id', statusId('completed'))->get();
        $kodeSurats = \App\Models\KodeSurat::where('aktif', true)->get();
        $pimpinanList = \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'pimpinan'))
            ->where('is_active', true)
            ->get(['id', 'name', 'jabatan', 'title']);
        return view('surat-keluar.create', compact('klasifikasi', 'suratMasuk', 'kodeSurats', 'pimpinanList'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', SuratKeluar::class);

        $data = $request->validate([
            'nomor_surat' => 'required|string|max:255|unique:surat_keluar',
            'tujuan_surat' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'klasifikasi_id' => 'required|exists:klasifikasi,id',
            'kode_surat_id' => 'nullable|exists:kode_surats,id',
            'surat_masuk_id' => 'nullable|exists:surat_masuk,id',
            'pimpinan_pengirim_id' => 'required|exists:users,id',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data['created_by'] = Auth::id();

        $lampiran = [];
        $files = $request->file('lampiran');

        if ($files && is_array($files)) {
            foreach ($files as $file) {
                try {
                    if (!$file instanceof UploadedFile || !$file->isValid()) {
                        Log::warning('SK lampiran skip: invalid file');
                        continue;
                    }
                    $originalName = $file->getClientOriginalName();
                    $mimeType = $file->getMimeType();
                    $fileSize = $file->getSize();
                    $ext = $file->getClientOriginalExtension() ?: 'bin';
                    $destDir = storage_path('app/public/surat-keluar');
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    $filename = Str::random(40) . '.' . $ext;
                    $file->move($destDir, $filename);
                    $path = 'surat-keluar/' . $filename;
                    $lampiran[] = [
                        'original_name' => $originalName,
                        'stored_path' => $path,
                        'mime_type' => $mimeType,
                        'file_size' => $fileSize,
                    ];
                    Log::info('SK lampiran stored', ['path' => $path]);
                } catch (\Throwable $e) {
                    Log::error('SK lampiran upload failed', ['error' => $e->getMessage()]);
                }
            }
        }

        $data['lampiran'] = !empty($lampiran) ? $lampiran : null;

        SuratKeluar::create($data);

        return redirect()->route('surat-keluar.index')
            ->with('success', 'Surat keluar berhasil dicatat.');
    }

    public function show(string $uuid)
    {
        $surat = SuratKeluar::with(['klasifikasi', 'pembuat', 'suratMasuk', 'kodeSurat'])
            ->where('uuid', $uuid)->firstOrFail();

        $this->authorize('view', $surat);

        return view('surat-keluar.show', compact('surat'));
    }

    public function edit(string $uuid, AksesKlasifikasiService $akses)
    {
        $surat = SuratKeluar::with(['klasifikasi', 'suratMasuk', 'kodeSurat'])
            ->where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $surat);

        $klasifikasi = $akses->getVisibleKlasifikasi(Auth::user());
        $suratMasuk = SuratMasuk::where('status_id', statusId('completed'))->get();
        $kodeSurats = \App\Models\KodeSurat::where('aktif', true)->get();
        $pimpinanList = \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'pimpinan'))
            ->where('is_active', true)
            ->get(['id', 'name', 'jabatan', 'title']);

        return view('surat-keluar.edit', compact('surat', 'klasifikasi', 'suratMasuk', 'kodeSurats', 'pimpinanList'));
    }

    public function update(Request $request, string $uuid)
    {
        $surat = SuratKeluar::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $surat);

        $data = $request->validate([
            'nomor_surat' => 'required|string|max:255|unique:surat_keluar,nomor_surat,' . $surat->id,
            'tujuan_surat' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'klasifikasi_id' => 'required|exists:klasifikasi,id',
            'kode_surat_id' => 'nullable|exists:kode_surats,id',
            'surat_masuk_id' => 'nullable|exists:surat_masuk,id',
            'pimpinan_pengirim_id' => 'required|exists:users,id',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'hapus_lampiran' => 'nullable|array',
            'hapus_lampiran.*' => 'string',
        ]);

        $lampiranBaru = [];
        $files = $request->file('lampiran');

        if ($files && is_array($files)) {
            foreach ($files as $file) {
                try {
                    if (!$file instanceof UploadedFile || !$file->isValid()) {
                        Log::warning('SK update lampiran skip: invalid file');
                        continue;
                    }
                    $originalName = $file->getClientOriginalName();
                    $mimeType = $file->getMimeType();
                    $fileSize = $file->getSize();
                    $ext = $file->getClientOriginalExtension() ?: 'bin';
                    $destDir = storage_path('app/public/surat-keluar');
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    $filename = Str::random(40) . '.' . $ext;
                    $file->move($destDir, $filename);
                    $path = 'surat-keluar/' . $filename;
                    $lampiranBaru[] = [
                        'original_name' => $originalName,
                        'stored_path' => $path,
                        'mime_type' => $mimeType,
                        'file_size' => $fileSize,
                    ];
                    Log::info('SK update lampiran stored', ['path' => $path]);
                } catch (\Throwable $e) {
                    Log::error('SK update lampiran upload failed', ['error' => $e->getMessage()]);
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

        $surat->update($data);

        return redirect()->route('surat-keluar.show', $uuid)
            ->with('success', 'Surat keluar berhasil diperbarui.');
    }

    public function destroy(string $uuid)
    {
        $surat = SuratKeluar::where('uuid', $uuid)->firstOrFail();

        $this->authorize('delete', $surat);

        $surat->delete();

        return redirect()->route('surat-keluar.index')
            ->with('success', 'Surat keluar berhasil dihapus.');
    }
}