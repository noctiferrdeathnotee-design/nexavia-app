<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\ResiController;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/tracking', [TrackingController::class, 'index'])
    ->name('tracking.index');

Route::post('/tracking/cari', [TrackingController::class, 'cari'])
    ->name('tracking.cari');

Route::get('/tracking/{resi}', [TrackingController::class, 'show'])
    ->name('tracking.show');

Route::middleware(['auth', 'admin.active'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/pengiriman', [PengirimanController::class, 'index'])
        ->name('pengiriman.index');

    Route::get('/pengiriman/create', [PengirimanController::class, 'create'])
        ->name('pengiriman.create');

    Route::post('/pengiriman', [PengirimanController::class, 'store'])
        ->name('pengiriman.store');

    Route::get('/pengiriman/{pengiriman}', [PengirimanController::class, 'show'])
        ->name('pengiriman.show');

    Route::post('/pengiriman/{pengiriman}/batal', [PengirimanController::class, 'batal'])
        ->name('pengiriman.batal');

    Route::post('/pengiriman/{pengiriman}/tracking', [TrackingController::class, 'store'])
        ->name('pengiriman.tracking.store');

    Route::get('/resi/{pengiriman}/print', [ResiController::class, 'print'])
        ->name('resi.print');

    Route::get('/resi/{pengiriman}/pdf', [ResiController::class, 'pdf'])
        ->name('resi.pdf');

    Route::get('/tarif', [TarifController::class, 'index'])
        ->name('tarif.index');

    Route::post('/tarif/cek', [TarifController::class, 'cek'])
        ->name('tarif.cek');

    Route::get('/laporan', [LaporanController::class, 'index'])
        ->name('laporan.index');

    Route::get('/laporan/pdf', [LaporanController::class, 'pdf'])
        ->name('laporan.pdf');
});
