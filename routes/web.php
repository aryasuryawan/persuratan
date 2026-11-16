<?php

use App\Http\Controllers\ArsipAksesController;
use App\Http\Controllers\ArsipMediaController;
use App\Http\Controllers\Api\AiAssistantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\DisposisiFollowUpController;
use App\Http\Controllers\KomitePersetujuanController;
use App\Http\Controllers\KotakSampahController;
use App\Http\Controllers\Master\DepartemenController;
use App\Http\Controllers\Master\DirektoratController;
use App\Http\Controllers\Master\DivisiController;
use App\Http\Controllers\Master\KlasifikasiController;
use App\Http\Controllers\Master\KodeSuratController;
use App\Http\Controllers\Master\OrganisasiPengirimController;
use App\Http\Controllers\Master\RoleController;
use App\Http\Controllers\Master\SekretarisPimpinanController;
use App\Http\Controllers\Master\SettingController;
use App\Http\Controllers\Master\StrukturController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\NotulensiRapimController;
use App\Http\Controllers\PenerimaanSuratController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\SuratKeputusanController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\VerifikasiSuratController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::prefix('follow-up')->name('follow-up.')->group(function () {
    Route::get('/{disposisi:uuid}', [DisposisiFollowUpController::class, 'publikForm'])->middleware('throttle:30,1')->name('publik');
    Route::post('/{disposisi:uuid}', [DisposisiFollowUpController::class, 'publikKirim'])->middleware('throttle:5,1')->name('publik.kirim');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard2', [DashboardController::class, 'dashboard2'])->name('dashboard2');
    Route::get('/kotak-sampah', [KotakSampahController::class, 'index'])->name('kotak-sampah.index');

    Route::prefix('penerimaan-surat')->name('penerimaan-surat.')->group(function () {
        Route::get('/', [PenerimaanSuratController::class, 'index'])->name('index');
        Route::get('/export', [PenerimaanSuratController::class, 'export'])->name('export');
        Route::get('/trash/list', [PenerimaanSuratController::class, 'trash'])->name('trash');
        Route::get('/create', [PenerimaanSuratController::class, 'create'])->name('create');
        Route::post('/', [PenerimaanSuratController::class, 'store'])->name('store');
        Route::get('/{uuid}/edit', [PenerimaanSuratController::class, 'edit'])->name('edit');
        Route::put('/{uuid}', [PenerimaanSuratController::class, 'update'])->name('update');
        Route::delete('/{uuid}', [PenerimaanSuratController::class, 'destroy'])->name('destroy');
        Route::post('/{uuid}/restore', [PenerimaanSuratController::class, 'restore'])->name('restore');
        Route::delete('/{uuid}/force-delete', [PenerimaanSuratController::class, 'forceDelete'])->name('force-delete');
        Route::get('/{uuid}/cetak-disposisi', [PenerimaanSuratController::class, 'cetakDisposisi'])->name('cetak-disposisi');
        Route::get('/{uuid}', [PenerimaanSuratController::class, 'show'])->name('show');
    });

    Route::prefix('verifikasi-surat')->name('verifikasi-surat.')->group(function () {
        Route::get('/', [VerifikasiSuratController::class, 'index'])->name('index');
        Route::post('/verify', [VerifikasiSuratController::class, 'verify'])->name('verify');
    });

    Route::prefix('disposisi')->name('disposisi.')->group(function () {
        Route::get('/', [DisposisiController::class, 'index'])->name('index');
        Route::post('/', [DisposisiController::class, 'store'])->name('store');
        Route::get('/pelaksana-options', [DisposisiController::class, 'pelaksanaOptions'])->name('pelaksana-options');
        Route::put('/{disposisi}', [DisposisiController::class, 'update'])->name('update');
        Route::delete('/{disposisi}', [DisposisiController::class, 'destroy'])->name('destroy');
        Route::post('/{disposisi}/follow-up', [DisposisiFollowUpController::class, 'store'])->name('follow-up.store');
        Route::put('/{disposisi}/follow-up/{followUp}', [DisposisiFollowUpController::class, 'update'])->name('follow-up.update');
        Route::post('/{disposisi}/pindah', [DisposisiController::class, 'pindah'])->name('pindah');
    });

    Route::prefix('surat-keluar')->name('surat-keluar.')->group(function () {
        Route::get('/', [SuratKeluarController::class, 'index'])->name('index');
        Route::get('/export', [SuratKeluarController::class, 'export'])->name('export');
        Route::get('/trash/list', [SuratKeluarController::class, 'trash'])->name('trash');
        Route::get('/create', [SuratKeluarController::class, 'create'])->name('create');
        Route::post('/', [SuratKeluarController::class, 'store'])->name('store');
        Route::get('/{uuid}/edit', [SuratKeluarController::class, 'edit'])->name('edit');
        Route::put('/{uuid}', [SuratKeluarController::class, 'update'])->name('update');
        Route::delete('/{uuid}', [SuratKeluarController::class, 'destroy'])->name('destroy');
        Route::post('/{uuid}/restore', [SuratKeluarController::class, 'restore'])->name('restore');
        Route::delete('/{uuid}/force-delete', [SuratKeluarController::class, 'forceDelete'])->name('force-delete');
        Route::get('/{uuid}', [SuratKeluarController::class, 'show'])->name('show');
    });

    Route::resource('surat-keputusan', SuratKeputusanController::class);
    Route::get('surat-keputusan/trash/list', [SuratKeputusanController::class, 'trash'])->name('surat-keputusan.trash');
    Route::post('surat-keputusan/{uuid}/restore', [SuratKeputusanController::class, 'restore'])->name('surat-keputusan.restore');
    Route::delete('surat-keputusan/{uuid}/force-delete', [SuratKeputusanController::class, 'forceDelete'])->name('surat-keputusan.force-delete');

    Route::resource('komite-persetujuan', KomitePersetujuanController::class);
    Route::get('komite-persetujuan/trash/list', [KomitePersetujuanController::class, 'trash'])->name('komite-persetujuan.trash');
    Route::post('komite-persetujuan/{uuid}/restore', [KomitePersetujuanController::class, 'restore'])->name('komite-persetujuan.restore');
    Route::delete('komite-persetujuan/{uuid}/force-delete', [KomitePersetujuanController::class, 'forceDelete'])->name('komite-persetujuan.force-delete');

    Route::resource('notulensi-rapim', NotulensiRapimController::class);
    Route::get('notulensi-rapim/trash/list', [NotulensiRapimController::class, 'trash'])->name('notulensi-rapim.trash');
    Route::post('notulensi-rapim/{uuid}/restore', [NotulensiRapimController::class, 'restore'])->name('notulensi-rapim.restore');
    Route::delete('notulensi-rapim/{uuid}/force-delete', [NotulensiRapimController::class, 'forceDelete'])->name('notulensi-rapim.force-delete');

    Route::prefix('arsip/{modul}/{uuid}')->name('arsip.akses.')->group(function () {
        Route::get('/akses', [ArsipAksesController::class, 'listAkses'])->name('list-akses');
        Route::post('/akses', [ArsipAksesController::class, 'assignAkses'])->name('assign-akses');
        Route::delete('/akses/{userId}', [ArsipAksesController::class, 'removeAkses'])->name('remove-akses');
    });

    Route::prefix('arsip-media')->name('arsip-media.')->group(function () {
        Route::get('/lampiran/{lampiran}', [ArsipMediaController::class, 'lampiran'])->name('lampiran.download');
        Route::get('/foto/{foto}', [ArsipMediaController::class, 'foto'])->name('foto.stream');
    });

    Route::prefix('tracking')->name('tracking.')->group(function () {
        Route::get('/', [TrackingController::class, 'index'])->name('index');
    });

    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('direktorats', DirektoratController::class);
        Route::resource('strukturs', StrukturController::class);
        Route::resource('organisasi-pengirims', OrganisasiPengirimController::class);
        Route::resource('divisis', DivisiController::class);
        Route::resource('departemens', DepartemenController::class);
        Route::resource('roles', RoleController::class);
        Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
        Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
        Route::resource('kode-surats', KodeSuratController::class);
        Route::resource('klasifikasi', KlasifikasiController::class);
        Route::resource('topik', \App\Http\Controllers\Master\TopikController::class);
        Route::resource('users', UserController::class);
        Route::resource('sekretaris-pimpinan', SekretarisPimpinanController::class)->except(['edit', 'update']);
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-smtp', [SettingController::class, 'testSmtp'])->name('settings.test-smtp');
        Route::post('/settings/test-ai', [SettingController::class, 'testAi'])->name('settings.test-ai');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/user-search', [UserController::class, 'search'])->name('user-search');
});

    Route::middleware(['auth', 'verified'])->prefix('api')->name('api.')->group(function () {
        Route::get('/surat-masuk', [PenerimaanSuratController::class, 'index'])->name('surat-masuk');
        Route::get('/surat-keluar', [SuratKeluarController::class, 'index'])->name('surat-keluar');
        Route::post('/surat-masuk/{uuid}/akses', [PenerimaanSuratController::class, 'assignAkses'])->name('surat-masuk.assign-akses');
        Route::delete('/surat-masuk/{uuid}/akses/{userId}', [PenerimaanSuratController::class, 'removeAkses'])->name('surat-masuk.remove-akses');
        Route::get('/surat-masuk/{uuid}/akses', [PenerimaanSuratController::class, 'listAkses'])->name('surat-masuk.list-akses');
        Route::get('/tracking/chart', [TrackingController::class, 'chart'])->name('tracking.chart');
        Route::get('/disposisi/pelaksana-options', [DisposisiController::class, 'pelaksanaOptions'])->name('disposisi.pelaksana-options');
        Route::get('/dashboard/chart', [DashboardController::class, 'chart'])->name('dashboard.chart');
        Route::post('/ai/ask', [AiAssistantController::class, 'ask'])->middleware('throttle:ai-ask')->name('ai.ask');
    });

require __DIR__.'/auth.php';
