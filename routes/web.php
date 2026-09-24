<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// 1. Entry point (Home & Login) - Defaults to Login page
Route::get('/', [AuthController::class, 'showLogin'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 2. Authenticated Routes
Route::middleware('auth')->group(function () {
    // Common academic references
    Route::inertia('/katalog', 'Katalog/Index')->name('katalog');
    Route::inertia('/panduan', 'Panduan/Index')->name('panduan');
    Route::inertia('/prosedur', 'Prosedur/Index')->name('prosedur');
    Route::inertia('/profile', 'Profile/Index')->name('profile');

    // ==========================================
    // PORTAL MAHASISWA
    // ==========================================
    Route::middleware('role:mahasiswa')->group(function () {
        Route::inertia('/dashboard', 'Dashboard')->name('dashboard');

        Route::prefix('pendaftaran')->name('pendaftaran.')->group(function () {
            Route::inertia('/judul', 'Pendaftaran/Judul')->name('judul');
            Route::inertia('/bimbingan', 'Pendaftaran/Bimbingan')->name('bimbingan');
            Route::inertia('/sempro', 'Pendaftaran/Sempro')->name('sempro');
            Route::inertia('/sidang', 'Pendaftaran/Sidang')->name('sidang');
        });
    });

    // ==========================================
    // PORTAL DOSEN (PEMBIMBING & PENGUJI)
    // ==========================================
    Route::middleware('role:dosen')->prefix('dosen')->name('dosen.')->group(function () {
        Route::inertia('/dashboard', 'Dosen/Dashboard')->name('dashboard');
        Route::inertia('/bimbingan', 'Dosen/Bimbingan')->name('bimbingan');
        Route::inertia('/penilaian', 'Dosen/Penilaian')->name('penilaian');
    });

    // ==========================================
    // PORTAL KAPRODI / KOORDINATOR TA
    // ==========================================
    Route::middleware('role:kaprodi,koordinator')->prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::inertia('/dashboard', 'Kaprodi/Dashboard')->name('dashboard');
        Route::inertia('/penjadwalan', 'Koordinator/Penjadwalan')->name('penjadwalan');
        Route::inertia('/monitoring', 'Koordinator/Monitoring')->name('monitoring');
        Route::inertia('/persetujuan', 'Kaprodi/Persetujuan')->name('persetujuan');
    });

    // Backward compatibility aliases for /koordinator routes
    Route::middleware('role:kaprodi,koordinator')->prefix('koordinator')->name('koordinator.')->group(function () {
        Route::get('/penjadwalan', fn () => redirect()->route('kaprodi.penjadwalan'))->name('penjadwalan');
        Route::get('/monitoring', fn () => redirect()->route('kaprodi.monitoring'))->name('monitoring');
    });

    // ==========================================
    // PORTAL TENDIK (TENAGA KEPENDIDIKAN / ADMIN)
    // ==========================================
    Route::middleware('role:tendik')->prefix('tendik')->name('tendik.')->group(function () {
        Route::inertia('/dashboard', 'Tendik/Dashboard')->name('dashboard');
        Route::inertia('/verifikasi', 'Tendik/Verifikasi')->name('verifikasi');
        Route::inertia('/ruangan', 'Tendik/Ruangan')->name('ruangan');
        Route::inertia('/arsip', 'Tendik/Arsip')->name('arsip');
        Route::inertia('/mahasiswa', 'Tendik/Mahasiswa')->name('mahasiswa');
    });
});
