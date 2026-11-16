<?php

namespace App\Http\Controllers;

use App\Models\Klasifikasi;
use App\Models\StatusSurat;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\Topik;
use App\Services\AksesKlasifikasiService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SuratKeluarController extends Controller
{
    public function index(Request $request, \App\Services\SuratScopeService $scope)
    {
        $this->authorize('viewAny', SuratKeluar::class);
        $user = Auth::user();

        if ($request->ajax()) {
            $filters = $request->only(['search', 'klasifikasi_id', 'topik_id', 'kode_surat_id', 'periode_dari', 'periode_sampai', 'inputer_id']);
            $query = SuratKeluar::with(['klasifikasi', 'pembuat', 'kodeSurat', 'topiks']);
            $query = $scope->scopeSuratKeluarForUser($query, $user);

            if (!empty($filters['search'])) {
                $s = $filters['search'];
                $query->where(function ($q) use ($s) {
                    $q->where('nomor_surat', 'like', "%{$s}%")
                      ->orWhere('perihal', 'like', "%{$s}%")
                      ->orWhere('tujuan_surat', 'like', "%{$s}%");
                });
            }
            if (!empty($filters['klasifikasi_id'])) {
                $query->where('klasifikasi_id', $filters['klasifikasi_id']);
            }
            if (!empty($filters['topik_id'])) {
                $query->whereHas('topiks', fn($q) => $q->where('topik.id', $filters['topik_id']));
            }
            if (!empty($filters['kode_surat_id'])) {
                $query->where('kode_surat_id', $filters['kode_surat_id']);
            }
            if (!empty($filters['periode_dari'])) {
                $query->whereDate('tanggal_surat', '>=', $filters['periode_dari']);
            }
            if (!empty($filters['periode_sampai'])) {
                $query->whereDate('tanggal_surat', '<=', $filters['periode_sampai']);
            }
            if (!empty($filters['inputer_id'])) {
                $query->where('created_by', $filters['inputer_id']);
            }

            $surat = $query->latest()->paginate($request->get('per_page', 25));

            $data = $surat->through(fn($s) => [
                'uuid' => $s->uuid,
                'nomor_surat' => $s->nomor_surat,
                'kode_surat' => $s->kodeSurat?->kode ?? '-',
                'tujuan_surat' => $s->tujuan_surat,
                'perihal' => $s->perihal,
                'tanggal_surat' => $s->tanggal_surat->format('d/m/Y'),
                'klasifikasi' => $s->klasifikasi->nama,
                'klasifikasi_color' => $s->klasifikasi->color,
                'topiks' => $s->topiks->map(fn($t) => ['nama' => $t->nama, 'color' => $t->color])->toArray(),
            ]);

            return response()->json($data);
        }

        $klasifikasi = Klasifikasi::orderBy('nama')->get();
        $topiks = Topik::orderBy('nama')->get();
        $kodeSurats = \App\Models\KodeSurat::where('aktif', true)->get();
        $inputers = \App\Models\User::whereIn('id', SuratKeluar::query()->whereNotNull('created_by')->distinct()->pluck('created_by'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $baseQuery = SuratKeluar::query();
        $baseQuery = $scope->scopeSuratKeluarForUser($baseQuery, $user, true);
        $total = (clone $baseQuery)->count();
        $hariIni = (clone $baseQuery)->whereDate('created_at', today())->count();

        return view('surat-keluar.index', compact('klasifikasi', 'topiks', 'kodeSurats', 'total', 'hariIni', 'inputers'));
    }

    public function create(AksesKlasifikasiService $akses)
    {
        $this->authorize('create', SuratKeluar::class);

        $klasifikasi = $akses->getVisibleKlasifikasi(Auth::user());
        $topiks = Topik::orderBy('nama')->get();
        $suratMasuk = SuratMasuk::where('status_id', statusId('completed'))->get();
        $kodeSurats = \App\Models\KodeSurat::where('aktif', true)->get();
        $pimpinanList = \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'pimpinan'))
            ->where('is_active', true)
            ->get(['id', 'name', 'jabatan', 'title']);
        $rahasiaKlasifikasiIds = $klasifikasi->filter(fn($k) => $k->isRahasia())->pluck('id')->values();
        return view('surat-keluar.create', compact('klasifikasi', 'topiks', 'suratMasuk', 'kodeSurats', 'pimpinanList', 'rahasiaKlasifikasiIds'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', SuratKeluar::class);

        // Parse topik_ids from JSON string (Alpine.js hidden input)
        $rawTopikIds = $request->input('topik_ids');
        if (is_string($rawTopikIds)) {
            $decoded = json_decode($rawTopikIds, true);
            $request->merge(['topik_ids' => is_array($decoded) ? $decoded : []]);
        }

        $data = $request->validate([
            'nomor_surat' => 'required|string|max:255|unique:surat_keluar',
            'tujuan_surat' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'klasifikasi_id' => 'required|exists:klasifikasi,id',
            'kode_surat_id' => 'nullable|exists:kode_surats,id',
            'surat_masuk_id' => 'nullable|exists:surat_masuk,id',
            'pimpinan_pengirim_id' => 'required|exists:users,id',
            'topik_ids' => 'nullable|array',
            'topik_ids.*' => 'exists:topik,id',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data['created_by'] = Auth::id();
        $data['hanya_pembuat_pimpinan'] = $request->boolean('hanya_pembuat_pimpinan');
        $topikIds = $data['topik_ids'] ?? [];
        unset($data['topik_ids']);

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

        $surat = SuratKeluar::latest()->first();
        $surat->topiks()->sync($topikIds);

        return redirect()->route('surat-keluar.index')
            ->with('success', 'Surat keluar berhasil dicatat.');
    }

    public function show(string $uuid)
    {
        $surat = SuratKeluar::with(['klasifikasi', 'topiks', 'pembuat', 'suratMasuk', 'kodeSurat'])
            ->where('uuid', $uuid)->firstOrFail();

        $this->authorize('view', $surat);

        return view('surat-keluar.show', compact('surat'));
    }

    public function edit(string $uuid, AksesKlasifikasiService $akses)
    {
        $surat = SuratKeluar::with(['klasifikasi', 'topiks', 'suratMasuk', 'kodeSurat'])
            ->where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $surat);

        $klasifikasi = $akses->getVisibleKlasifikasi(Auth::user());
        $topiks = Topik::orderBy('nama')->get();
        $selectedTopiks = $surat->topiks->pluck('id')->toArray();
        $suratMasuk = SuratMasuk::where('status_id', statusId('completed'))->get();
        $kodeSurats = \App\Models\KodeSurat::where('aktif', true)->get();
        $pimpinanList = \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'pimpinan'))
            ->where('is_active', true)
            ->get(['id', 'name', 'jabatan', 'title']);

        $rahasiaKlasifikasiIds = $klasifikasi->filter(fn($k) => $k->isRahasia())->pluck('id')->values();

        return view('surat-keluar.edit', compact('surat', 'klasifikasi', 'topiks', 'selectedTopiks', 'suratMasuk', 'kodeSurats', 'pimpinanList', 'rahasiaKlasifikasiIds'));
    }

    public function update(Request $request, string $uuid)
    {
        $surat = SuratKeluar::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $surat);

        // Parse topik_ids from JSON string (Alpine.js hidden input)
        $rawTopikIds = $request->input('topik_ids');
        if (is_string($rawTopikIds)) {
            $decoded = json_decode($rawTopikIds, true);
            $request->merge(['topik_ids' => is_array($decoded) ? $decoded : []]);
        }

        $data = $request->validate([
            'nomor_surat' => 'required|string|max:255|unique:surat_keluar,nomor_surat,' . $surat->id,
            'tujuan_surat' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'klasifikasi_id' => 'required|exists:klasifikasi,id',
            'kode_surat_id' => 'nullable|exists:kode_surats,id',
            'surat_masuk_id' => 'nullable|exists:surat_masuk,id',
            'pimpinan_pengirim_id' => 'required|exists:users,id',
            'topik_ids' => 'nullable|array',
            'topik_ids.*' => 'exists:topik,id',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'hapus_lampiran' => 'nullable|array',
            'hapus_lampiran.*' => 'string',
        ]);

        $topikIds = $data['topik_ids'] ?? [];
        unset($data['topik_ids']);
        $data['hanya_pembuat_pimpinan'] = $request->boolean('hanya_pembuat_pimpinan');

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
        $surat->topiks()->sync($topikIds);

        return redirect()->route('surat-keluar.show', $uuid)
            ->with('success', 'Surat keluar berhasil diperbarui.');
    }

    public function export(Request $request, \App\Services\SuratScopeService $scope)
    {
        $this->authorize('viewAny', SuratKeluar::class);
        $user = Auth::user();
        $filters = $request->only(['search', 'klasifikasi_id', 'topik_id', 'kode_surat_id', 'periode_dari', 'periode_sampai', 'inputer_id']);

        $query = SuratKeluar::with(['klasifikasi', 'pembuat', 'kodeSurat', 'topiks']);
        $query = $scope->scopeSuratKeluarForUser($query, $user);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('nomor_surat', 'like', "%{$s}%")
                  ->orWhere('perihal', 'like', "%{$s}%")
                  ->orWhere('tujuan_surat', 'like', "%{$s}%");
            });
        }
        if (!empty($filters['klasifikasi_id'])) {
            $query->where('klasifikasi_id', $filters['klasifikasi_id']);
        }
        if (!empty($filters['topik_id'])) {
            $query->whereHas('topiks', fn($q) => $q->where('topik.id', $filters['topik_id']));
        }
        if (!empty($filters['kode_surat_id'])) {
            $query->where('kode_surat_id', $filters['kode_surat_id']);
        }
        if (!empty($filters['periode_dari'])) {
            $query->whereDate('tanggal_surat', '>=', $filters['periode_dari']);
        }
        if (!empty($filters['periode_sampai'])) {
            $query->whereDate('tanggal_surat', '<=', $filters['periode_sampai']);
        }
        if (!empty($filters['inputer_id'])) {
            $query->where('created_by', $filters['inputer_id']);
        }

        $query->latest('tanggal_surat');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['No. Surat', 'Kode Surat', 'Tujuan', 'Perihal', 'Tanggal Surat', 'Klasifikasi', 'Topik', 'Inputer'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        $query->chunk(200, function ($rows) use ($sheet, &$row) {
            foreach ($rows as $s) {
                $sheet->fromArray([
                    $s->nomor_surat,
                    $s->kodeSurat?->kode,
                    $s->tujuan_surat,
                    $s->perihal,
                    optional($s->tanggal_surat)->format('d/m/Y'),
                    $s->klasifikasi?->nama,
                    $s->topiks->pluck('nama')->implode(', '),
                    $s->pembuat?->name,
                ], null, 'A' . $row);
                $row++;
            }
        });

        $filename = 'surat-keluar-' . now()->format('Ymd-His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function trash(Request $request)
    {
        $this->authorize('viewAny', SuratKeluar::class);

        $query = SuratKeluar::onlyTrashed();

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_surat', 'like', "%{$q}%")
                    ->orWhere('tujuan_surat', 'like', "%{$q}%")
                    ->orWhere('perihal', 'like', "%{$q}%");
            });
        }

        $items = $query->latest('deleted_at')->paginate($request->get('per_page', 25));

        return view('surat-keluar.trash', compact('items'));
    }

    public function destroy(string $uuid)
    {
        $surat = SuratKeluar::where('uuid', $uuid)->firstOrFail();

        $this->authorize('delete', $surat);

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

        return redirect()->route('surat-keluar.index')
            ->with('success', 'Surat keluar berhasil dihapus.');
    }

    public function restore(string $uuid)
    {
        $surat = SuratKeluar::withTrashed()->where('uuid', $uuid)->firstOrFail();

        if (! Auth::user()->isCreatorOrAdmin($surat->created_by)) {
            abort(403, 'Anda tidak berhak memulihkan surat ini.');
        }

        $surat->restore();

        return redirect()->route('surat-keluar.show', $uuid)
            ->with('success', 'Surat keluar berhasil dipulihkan.');
    }

    public function forceDelete(string $uuid)
    {
        $surat = SuratKeluar::withTrashed()->where('uuid', $uuid)->firstOrFail();

        if (! Auth::user()->isCreatorOrAdmin($surat->created_by)) {
            abort(403, 'Anda tidak berhak menghapus permanen surat ini.');
        }

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

        return redirect()->route('surat-keluar.index')
            ->with('success', 'Surat keluar berhasil dihapus permanen.');
    }
}