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
        return view('penerimaan-surat.create', compact('klasifikasi'));
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

        SuratMasuk::create($data);

        return redirect()->route('penerimaan-surat.index')
            ->with('success', 'Surat masuk berhasil dicatat.');
    }

    public function show(string $uuid, AksesKlasifikasiService $akses)
    {
        $surat = SuratMasuk::with(['klasifikasi', 'status', 'pembuat', 'verifikator', 'riwayatStatus.statusKe', 'riwayatStatus.dilakukanOleh.role', 'riwayatStatus.atasNamaPimpinan.role', 'riwayatStatus.disposisi.kepadaUser.direktorat', 'riwayatStatus.disposisi.kepadaUser.divisi', 'riwayatStatus.disposisi.kepadaUser.departemen', 'disposisi.dariUser', 'disposisi.kepadaUser.direktorat', 'disposisi.kepadaUser.divisi', 'disposisi.kepadaUser.departemen', 'disposisi.lampiran', 'disposisi.followUps.user'])
            ->where('uuid', $uuid)->firstOrFail();

        if (!$akses->canAccess(Auth::user(), $surat->klasifikasi)) {
            abort(403, 'Anda tidak memiliki akses ke surat ini.');
        }

        return view('penerimaan-surat.show', compact('surat'));
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
}
