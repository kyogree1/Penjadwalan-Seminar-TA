<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Mahasiswa\BimbinganController;
use App\Http\Controllers\Mahasiswa\PendaftaranJadwalController;
use App\Http\Controllers\Mahasiswa\PendaftaranJudulController;
use App\Http\Controllers\Mahasiswa\PendaftaranSemproController;
use App\Http\Controllers\Mahasiswa\PendaftaranSidangController;
use App\Http\Controllers\ProfileController;
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

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // ==========================================
    // PORTAL MAHASISWA
    // ==========================================
    Route::middleware('role:mahasiswa')->group(function () {
        Route::inertia('/dashboard', 'Dashboard')->name('dashboard');

        Route::prefix('pendaftaran')->name('pendaftaran.')->group(function () {
            // 1. Pengajuan Judul
            Route::get('/judul', [PendaftaranJudulController::class, 'index'])->name('judul');
            Route::post('/judul', [PendaftaranJudulController::class, 'store']);

            // 2. Logbook Bimbingan
            Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan');
            Route::post('/bimbingan', [BimbinganController::class, 'store']);

            // 3. Pendaftaran & Jadwal
            Route::get('/jadwal', [PendaftaranJadwalController::class, 'index'])->name('jadwal');

            // 4. Seminar Proposal (Sempro)
            Route::get('/sempro', [PendaftaranSemproController::class, 'index'])->name('sempro');
            Route::post('/sempro', [PendaftaranSemproController::class, 'store']);

            // 5. Sidang Tugas Akhir
            Route::get('/sidang', [PendaftaranSidangController::class, 'index'])->name('sidang');
            Route::post('/sidang', [PendaftaranSidangController::class, 'store']);
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
        Route::inertia('/dosen', 'Kaprodi/Dosen')->name('dosen');
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
