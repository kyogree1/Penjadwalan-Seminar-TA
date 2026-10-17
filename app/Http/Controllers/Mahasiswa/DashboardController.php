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
        $approvedTitle = PengajuanJudul::with(['pembimbing1:id,name', 'pembimbing2:id,name'])
            ->where('mahasiswa_id', $user->id)->where('status', 'disetujui')
            ->latest()->orderByDesc('id')->first();
        $sempro = PendaftaranSempro::where('mahasiswa_id', $user->id)->latest()->first();
        $sidang = PendaftaranSidang::where('mahasiswa_id', $user->id)->latest()->first();

        // 1b. Periode Aktif Pendaftaran
        $activePeriods = \Illuminate\Support\Facades\DB::table('schedule_periods')
            ->where('is_open', true)
            ->get()
            ->map(function ($period) {
                $isSempro = $period->tipe === 'sempro';
                $start = \Carbon\Carbon::parse($period->start_date)->translatedFormat('d M');
                $end = \Carbon\Carbon::parse($period->end_date)->translatedFormat('d M Y');

                return [
                    'id' => (string) $period->id,
                    'type' => $period->tipe,
                    'title' => $period->name,
                    'semester' => 'Genap',
                    'academicYear' => '2025/2026',
                    'gelombang' => (string) $period->gelombang,
                    'startDate' => $start,
                    'endDate' => $end,
                    'colorTheme' => $isSempro
                        ? [
                            'bg' => 'bg-emerald-50/70 dark:bg-emerald-950/20',
                            'badgeBg' => 'bg-emerald-100 dark:bg-emerald-900/60',
                            'text' => 'text-emerald-900 dark:text-emerald-200',
                            'accentText' => 'text-emerald-700 dark:text-emerald-300',
                            'border' => 'border-emerald-200 dark:border-emerald-800',
                        ]
                        : [
                            'bg' => 'bg-purple-50/70 dark:bg-purple-950/20',
                            'badgeBg' => 'bg-purple-100 dark:bg-purple-900/60',
                            'text' => 'text-purple-900 dark:text-purple-200',
                            'accentText' => 'text-purple-700 dark:text-purple-300',
                            'border' => 'border-purple-200 dark:border-purple-800',
                        ],
                ];
            });

        // 2. Total Bimbingan yang telah disetujui (delivered)
        $totalBimbinganAcc = Bimbingan::where('mahasiswa_id', $user->id)
            ->where('status', 'delivered')
            ->count();

        // Only this student's published exams belong in the personal agenda.
        $jadwalList = Penjadwalan::with(['pembimbing1:id,name', 'pembimbing2:id,name', 'penguji1:id,name', 'penguji2:id,name'])
            ->where('mahasiswa_id', $user->id)
            ->where('status', 'terpublikasi')
            ->orderBy('tanggal')->orderBy('jam_mulai')->orderBy('id')
            ->get();
        $jadwalAktif = $jadwalList->last();

        // 4. Hitung Roadmap Sempro
        $semproSteps = [
            [
                'name' => 'Pengajuan',
                'status' => $sempro ? 'completed' : 'pending',
                'date' => $sempro ? $sempro->created_at->translatedFormat('d M Y') : 'Belum diajukan',
            ],
            [
                'name' => 'Verifikasi',
                'status' => match ($sempro?->status) {
                    'verifikasi_tendik', 'terjadwal', 'selesai' => 'completed',
                    'menunggu' => 'in_progress',
                    default => 'pending',
                },
                'date' => match ($sempro?->status) {
                    'verifikasi_tendik', 'terjadwal', 'selesai' => 'Diverifikasi',
                    'menunggu' => 'Antrean Tendik',
                    default => 'Menunggu pengajuan',
                },
            ],
            [
                'name' => 'Penilaian',
                'status' => match ($sempro?->status) {
                    'selesai' => 'completed',
                    'terjadwal' => 'in_progress',
                    default => 'pending',
                },
                'date' => match ($sempro?->status) {
                    'selesai' => 'Selesai (nilai belum tersedia)',
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
                'status' => match ($sidang?->status) {
                    'verifikasi_tendik', 'terjadwal', 'selesai' => 'completed',
                    'menunggu' => 'in_progress',
                    default => 'pending',
                },
                'date' => match ($sidang?->status) {
                    'verifikasi_tendik', 'terjadwal', 'selesai' => 'Diverifikasi',
                    'menunggu' => 'Antrean Tendik',
                    default => 'Menunggu pengajuan',
                },
            ],
            [
                'name' => 'Penilaian',
                'status' => match ($sidang?->status) {
                    'selesai' => 'completed',
                    'terjadwal' => 'in_progress',
                    default => 'pending',
                },
                'date' => match ($sidang?->status) {
                    'selesai' => 'Selesai (nilai belum tersedia)',
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
            'dbApprovedTitle' => $approvedTitle,
            'dbSempro' => $sempro,
            'dbSidang' => $sidang,
            'activePeriods' => $activePeriods,
            'dbTotalBimbinganAcc' => $totalBimbinganAcc,
            'dbJadwal' => $jadwalAktif,
            'dbJadwalList' => $jadwalList,
            'dbSemproSteps' => $semproSteps,
            'dbSidangSteps' => $sidangSteps,
        ]);
    }
}
