<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();

        // 1. Data Pengajuan
        $judul = PengajuanJudul::where('mahasiswa_id', $user->id)->latest()->first();
        $sempro = PendaftaranSempro::where('mahasiswa_id', $user->id)->latest()->first();
        $sidang = PendaftaranSidang::where('mahasiswa_id', $user->id)->latest()->first();

        // 2. Total Bimbingan yang telah disetujui (delivered)
        $totalBimbinganAcc = Bimbingan::where('mahasiswa_id', $user->id)
            ->where('status', 'delivered')
            ->count();

        // 3. Jadwal jika sudah dijadwalkan
        $jadwalAktif = Penjadwalan::with(['ruangan:id,nama_ruangan,gedung', 'pembimbing1:id,name', 'penguji1:id,name'])
            ->where('mahasiswa_id', $user->id)
            ->where('status', 'terpublikasi')
            ->latest('tanggal')
            ->first();

        // 4. Hitung Roadmap Sempro
        $semproSteps = [
            [
                'name' => 'Pengajuan',
                'status' => $sempro ? 'completed' : 'pending',
                'date' => $sempro ? $sempro->created_at->translatedFormat('d M Y') : 'Belum diajukan',
            ],
            [
                'name' => 'Verifikasi',
                'status' => match($sempro?->status) {
                    'verifikasi_tendik', 'terjadwal', 'selesai' => 'completed',
                    'menunggu' => 'in_progress',
                    default => 'pending',
                },
                'date' => match($sempro?->status) {
                    'verifikasi_tendik', 'terjadwal', 'selesai' => 'Diverifikasi',
                    'menunggu' => 'Antrean Tendik',
                    default => 'Menunggu pengajuan',
                },
            ],
            [
                'name' => 'Penilaian',
                'status' => match($sempro?->status) {
                    'selesai' => 'completed',
                    'terjadwal' => 'in_progress',
                    default => 'pending',
                },
                'date' => match($sempro?->status) {
                    'selesai' => 'Lulus',
                    'terjadwal' => 'Terjadwal',
                    default => 'Belum seminar',
                },
            ],
            [
                'name' => 'Hasil Seminar',
                'status' => $sempro?->status === 'selesai' ? 'completed' : 'pending',
                'date' => $sempro?->status === 'selesai' ? 'Selesai' : 'TBA',
            ],
        ];

        // 5. Hitung Roadmap Sidang
        $sidangSteps = [
            [
                'name' => 'Pengajuan',
                'status' => $sidang ? 'completed' : 'pending',
                'date' => $sidang ? $sidang->created_at->translatedFormat('d M Y') : 'Belum diajukan',
            ],
            [
                'name' => 'Verifikasi',
                'status' => match($sidang?->status) {
                    'verifikasi_tendik', 'terjadwal', 'selesai' => 'completed',
                    'menunggu' => 'in_progress',
                    default => 'pending',
                },
                'date' => match($sidang?->status) {
                    'verifikasi_tendik', 'terjadwal', 'selesai' => 'Diverifikasi',
                    'menunggu' => 'Antrean Tendik',
                    default => 'Menunggu pengajuan',
                },
            ],
            [
                'name' => 'Penilaian',
                'status' => match($sidang?->status) {
                    'selesai' => 'completed',
                    'terjadwal' => 'in_progress',
                    default => 'pending',
                },
                'date' => match($sidang?->status) {
                    'selesai' => 'Lulus',
                    'terjadwal' => 'Terjadwal',
                    default => 'Belum sidang',
                },
            ],
            [
                'name' => 'Hasil Sidang',
                'status' => $sidang?->status === 'selesai' ? 'completed' : 'pending',
                'date' => $sidang?->status === 'selesai' ? 'Selesai' : 'TBA',
            ],
        ];

        return Inertia::render('Dashboard', [
            'dbJudul' => $judul,
            'dbSempro' => $sempro,
            'dbSidang' => $sidang,
            'dbTotalBimbinganAcc' => $totalBimbinganAcc,
            'dbJadwal' => $jadwalAktif,
            'dbSemproSteps' => $semproSteps,
            'dbSidangSteps' => $sidangSteps,
        ]);
    }
}
