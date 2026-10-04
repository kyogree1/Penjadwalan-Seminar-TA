<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dosen\BimbinganController as DosenBimbinganController;
use App\Http\Controllers\Dosen\DashboardController as DosenDashboardController;
use App\Http\Controllers\Dosen\PenilaianController as DosenPenilaianController;
use App\Http\Controllers\Kaprodi\DashboardController as KaprodiDashboardController;
use App\Http\Controllers\Kaprodi\DosenController as KaprodiDosenController;
use App\Http\Controllers\Kaprodi\PenjadwalanController as KaprodiPenjadwalanController;
use App\Http\Controllers\Kaprodi\PersetujuanController as KaprodiPersetujuanController;
use App\Http\Controllers\Mahasiswa\BimbinganController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\Mahasiswa\PendaftaranJadwalController;
use App\Http\Controllers\Mahasiswa\PendaftaranJudulController;
use App\Http\Controllers\Mahasiswa\PendaftaranSemproController;
use App\Http\Controllers\Mahasiswa\PendaftaranSidangController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tendik\ArsipController as TendikArsipController;
use App\Http\Controllers\Tendik\DashboardController as TendikDashboardController;
use App\Http\Controllers\Tendik\MahasiswaController as TendikMahasiswaController;
use App\Http\Controllers\Tendik\RuanganController as TendikRuanganController;
use App\Http\Controllers\Tendik\VerifikasiController as TendikVerifikasiController;
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
        Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');

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
        Route::get('/dashboard', [DosenDashboardController::class, 'index'])->name('dashboard');

        Route::get('/bimbingan', [DosenBimbinganController::class, 'index'])->name('bimbingan');
        Route::post('/bimbingan/sesi', [DosenBimbinganController::class, 'storeSesi'])->name('bimbingan.sesi');
        Route::patch('/bimbingan/{id}/paraf', [DosenBimbinganController::class, 'paraf'])->name('bimbingan.paraf');

        Route::get('/penilaian', [DosenPenilaianController::class, 'index'])->name('penilaian');
        Route::post('/penilaian/{id}', [DosenPenilaianController::class, 'store'])->name('penilaian.store');
    });

    // ==========================================
    // PORTAL KAPRODI / KOORDINATOR TA
    // ==========================================
    Route::middleware('role:kaprodi,koordinator')->prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::get('/dashboard', [KaprodiDashboardController::class, 'index'])->name('dashboard');

        Route::get('/penjadwalan', [KaprodiPenjadwalanController::class, 'index'])->name('penjadwalan');
        Route::post('/penjadwalan/generate-ga', [KaprodiPenjadwalanController::class, 'generateGa'])->name('penjadwalan.generate-ga');
        Route::post('/penjadwalan/simpan', [KaprodiPenjadwalanController::class, 'simpan'])->name('penjadwalan.simpan');
        Route::patch('/penjadwalan/{id}', [KaprodiPenjadwalanController::class, 'update'])->name('penjadwalan.update');

        Route::inertia('/monitoring', 'Koordinator/Monitoring')->name('monitoring');

        Route::get('/dosen', [KaprodiDosenController::class, 'index'])->name('dosen');
        Route::post('/dosen', [KaprodiDosenController::class, 'store'])->name('dosen.store');
        Route::put('/dosen/{id}', [KaprodiDosenController::class, 'update'])->name('dosen.update');
        Route::delete('/dosen/{id}', [KaprodiDosenController::class, 'destroy'])->name('dosen.destroy');

        Route::get('/persetujuan', [KaprodiPersetujuanController::class, 'index'])->name('persetujuan');
        Route::patch('/persetujuan/judul/{id}', [KaprodiPersetujuanController::class, 'updateJudul'])->name('persetujuan.judul');
        Route::patch('/persetujuan/sempro/{id}', [KaprodiPersetujuanController::class, 'updateSempro'])->name('persetujuan.sempro');
        Route::patch('/persetujuan/sidang/{id}', [KaprodiPersetujuanController::class, 'updateSidang'])->name('persetujuan.sidang');
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
        Route::get('/dashboard', [TendikDashboardController::class, 'index'])->name('dashboard');

        Route::get('/verifikasi', [TendikVerifikasiController::class, 'index'])->name('verifikasi');
        Route::patch('/verifikasi/sempro/{id}', [TendikVerifikasiController::class, 'verifySempro'])->name('verifikasi.sempro');
        Route::patch('/verifikasi/sidang/{id}', [TendikVerifikasiController::class, 'verifySidang'])->name('verifikasi.sidang');

        Route::get('/ruangan', [TendikRuanganController::class, 'index'])->name('ruangan');
        Route::post('/ruangan', [TendikRuanganController::class, 'store'])->name('ruangan.store');
        Route::put('/ruangan/{id}', [TendikRuanganController::class, 'update'])->name('ruangan.update');
        Route::delete('/ruangan/{id}', [TendikRuanganController::class, 'destroy'])->name('ruangan.destroy');

        Route::get('/arsip', [TendikArsipController::class, 'index'])->name('arsip');

        Route::get('/mahasiswa', [TendikMahasiswaController::class, 'index'])->name('mahasiswa');
        Route::post('/mahasiswa/{id}/reset-password', [TendikMahasiswaController::class, 'resetPassword'])->name('mahasiswa.reset-password');
    });
});
