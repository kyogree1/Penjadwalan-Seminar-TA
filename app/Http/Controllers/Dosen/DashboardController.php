<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        // 1. Cari sesi bimbingan menunggu paraf
        $pendingBimbingan = Bimbingan::where('dosen_id', $user->id)
            ->where('status', 'process')
            ->count();

        // 2. Cari jadwal menguji mendatang
        $jadwalUjiQuery = Penjadwalan::with(['mahasiswa:id,name,username,nim_nip'])
            ->where(function ($q) use ($user) {
                $q->where('penguji_1_id', $user->id)
                    ->orWhere('penguji_2_id', $user->id);
            })
            ->where('status', 'terpublikasi')
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai');

        $jadwalUjiCount = $jadwalUjiQuery->count();

        $upcomingExams = $jadwalUjiQuery->take(5)->get()->map(function ($j) use ($user) {
            $isPenguji1 = $j->penguji_1_id === $user->id;
            $peran = $isPenguji1 ? 'Penguji 1' : 'Penguji 2';
            $isSempro = $j->tipe === 'sempro';

            return [
                'id' => $j->id,
                'tipe' => $isSempro ? 'Seminar Proposal' : 'Sidang Tugas Akhir',
                'tipeBadge' => $isSempro
                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300'
                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
                'mahasiswa' => $j->mahasiswa?->name ?? 'Mahasiswa',
                'nim' => $j->mahasiswa?->nim_nip ?? $j->mahasiswa?->username ?? '-',
                'judul' => $j->catatan ?? 'Judul Tugas Akhir',
                'peran' => $peran,
                'tanggal' => $j->tanggal ? $j->tanggal->translatedFormat('l, d M Y') : '-',
                'jam' => substr($j->jam_mulai, 0, 5) . ' - ' . substr($j->jam_selesai, 0, 5) . ' WITA',
                'ruang' => $j->mode === 'online' ? ($j->link_online ?? 'Online Zoom/Meet') : 'Ruang Ujian IF',
                'href' => '/dosen/penilaian',
            ];
        });

        // 3. Mahasiswa bimbingan tugas akhir milik dosen ini
        $supervisedJudul = PengajuanJudul::with(['mahasiswa:id,name,username,nim_nip'])
            ->where(function ($q) use ($user) {
                $q->where('pembimbing_1_id', $user->id)
                    ->orWhere('pembimbing_2_id', $user->id);
            })
            ->where('status', 'disetujui')
            ->get();

        $mySupervisedStudents = $supervisedJudul->map(function ($judul) use ($user) {
            $mhsId = $judul->mahasiswa_id;
            $bimbinganSessions = Bimbingan::where('mahasiswa_id', $mhsId)
                ->where('dosen_id', $user->id)
                ->latest('tanggal')
                ->get();

            $pendingCount = $bimbinganSessions->where('status', 'process')->count();
            $deliveredCount = $bimbinganSessions->where('status', 'delivered')->count();
            $lastSession = $bimbinganSessions->first();

            // Cek tahapan berdasarkan pendaftaran
            $sempro = PendaftaranSempro::where('mahasiswa_id', $mhsId)->latest()->first();
            $sidang = PendaftaranSidang::where('mahasiswa_id', $mhsId)->latest()->first();

            $tahap = 'Bimbingan Proposal';
            $progress = min(100, (int) ($deliveredCount * 12.5)); // 8 sesi = 100%

            if ($sidang && in_array($sidang->status, ['terjadwal', 'selesai'])) {
                $tahap = $sidang->status === 'selesai' ? 'Lulus Sidang TA' : 'Pelaksanaan Sidang';
                $progress = 100;
            } elseif ($sempro && in_array($sempro->status, ['terjadwal', 'selesai'])) {
                $tahap = $sempro->status === 'selesai' ? 'Bimbingan Pasca Sempro' : 'Pelaksanaan Sempro';
                $progress = max(60, $progress);
            } elseif ($deliveredCount >= 8) {
                $tahap = 'Siap Daftar Sempro';
                $progress = 60;
            }

            return [
                'id' => $judul->id,
                'nama' => $judul->mahasiswa?->name ?? 'Mahasiswa',
                'nim' => $judul->mahasiswa?->nim_nip ?? $judul->mahasiswa?->username ?? '-',
                'judul' => $judul->judul_ta,
                'tahap' => $tahap,
                'progress' => $progress,
                'hasPendingLogbook' => $pendingCount > 0,
                'logbookPendingText' => $pendingCount > 0
                    ? "{$pendingCount} Logbook Menunggu Validasi"
                    : "{$deliveredCount} Sesi Terverifikasi",
                'terakhirBimbingan' => $lastSession ? $lastSession->tanggal->translatedFormat('d M Y') : 'Belum bimbingan',
            ];
        });

        // 4. Hitung distribusi tahapan bimbingan
        $totalAdvisees = max(1, $mySupervisedStudents->count());
        $stageProposal = $mySupervisedStudents->filter(fn ($s) => str_contains($s['tahap'], 'Proposal'))->count();
        $stageSempro = $mySupervisedStudents->filter(fn ($s) => str_contains($s['tahap'], 'Sempro'))->count();
        $stageSidang = $mySupervisedStudents->filter(fn ($s) => str_contains($s['tahap'], 'Sidang'))->count();

        $adviseeStages = [
            [
                'name' => 'Bimbingan Proposal',
                'count' => $stageProposal,
                'pct' => (int) round(($stageProposal / $totalAdvisees) * 100),
                'barColor' => 'bg-amber-500',
                'dotColor' => 'bg-amber-500',
                'href' => '/dosen/bimbingan',
            ],
            [
                'name' => 'Seminar Proposal',
                'count' => $stageSempro,
                'pct' => (int) round(($stageSempro / $totalAdvisees) * 100),
                'barColor' => 'bg-blue-500',
                'dotColor' => 'bg-blue-500',
                'href' => '/dosen/sempro',
            ],
            [
                'name' => 'Sidang Tugas Akhir',
                'count' => $stageSidang,
                'pct' => (int) round(($stageSidang / $totalAdvisees) * 100),
                'barColor' => 'bg-emerald-500',
                'dotColor' => 'bg-emerald-500',
                'href' => '/dosen/sidang',
            ],
        ];

        return Inertia::render('Dosen/Dashboard', [
            'stats' => [
                'pendingBimbingan' => $pendingBimbingan,
                'jadwalUjiCount' => $jadwalUjiCount,
            ],
            'mySupervisedStudents' => $mySupervisedStudents->values()->all(),
            'upcomingExams' => $upcomingExams->values()->all(),
            'adviseeStages' => $adviseeStages,
        ]);
    }
}
