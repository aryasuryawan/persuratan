<?php

use App\Http\Controllers\ArsipAksesController;
use App\Http\Controllers\ArsipMediaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\DisposisiFollowUpController;
use App\Http\Controllers\KomitePersetujuanController;
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

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard2', [DashboardController::class, 'dashboard2'])->name('dashboard2');

    Route::prefix('penerimaan-surat')->name('penerimaan-surat.')->group(function () {
        Route::get('/', [PenerimaanSuratController::class, 'index'])->name('index');
        Route::get('/create', [PenerimaanSuratController::class, 'create'])->name('create');
        Route::post('/', [PenerimaanSuratController::class, 'store'])->name('store');
        Route::get('/{uuid}/edit', [PenerimaanSuratController::class, 'edit'])->name('edit');
        Route::put('/{uuid}', [PenerimaanSuratController::class, 'update'])->name('update');
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
        Route::get('/create', [SuratKeluarController::class, 'create'])->name('create');
        Route::post('/', [SuratKeluarController::class, 'store'])->name('store');
        Route::get('/{uuid}/edit', [SuratKeluarController::class, 'edit'])->name('edit');
        Route::put('/{uuid}', [SuratKeluarController::class, 'update'])->name('update');
        Route::delete('/{uuid}', [SuratKeluarController::class, 'destroy'])->name('destroy');
        Route::get('/{uuid}', [SuratKeluarController::class, 'show'])->name('show');
    });

    Route::resource('surat-keputusan', SuratKeputusanController::class);
    Route::resource('komite-persetujuan', KomitePersetujuanController::class);
    Route::resource('notulensi-rapim', NotulensiRapimController::class);

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
        Route::resource('kode-surats', KodeSuratController::class);
        Route::resource('klasifikasi', KlasifikasiController::class);
        Route::resource('users', UserController::class);
        Route::resource('sekretaris-pimpinan', SekretarisPimpinanController::class)->except(['edit', 'update']);
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::get('/surat-masuk', [PenerimaanSuratController::class, 'index'])->name('surat-masuk');
    Route::post('/surat-masuk/{uuid}/akses', [PenerimaanSuratController::class, 'assignAkses'])->name('surat-masuk.assign-akses');
    Route::delete('/surat-masuk/{uuid}/akses/{userId}', [PenerimaanSuratController::class, 'removeAkses'])->name('surat-masuk.remove-akses');
    Route::get('/surat-masuk/{uuid}/akses', [PenerimaanSuratController::class, 'listAkses'])->name('surat-masuk.list-akses');
    Route::get('/user-search', [UserController::class, 'search'])->name('user-search');
    Route::get('/tracking/chart', [TrackingController::class, 'chart'])->name('tracking.chart');
    Route::get('/disposisi/pelaksana-options', [DisposisiController::class, 'pelaksanaOptions'])->name('disposisi.pelaksana-options');
    Route::get('/dashboard/chart', [DashboardController::class, 'chart'])->name('dashboard.chart');
});

require __DIR__.'/auth.php';
