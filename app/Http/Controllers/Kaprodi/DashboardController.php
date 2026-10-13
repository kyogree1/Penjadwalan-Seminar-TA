<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $pendingCounts = [
            'judul' => PengajuanJudul::where('status', 'menunggu')->count(),
            'sempro' => PendaftaranSempro::where('status', 'menunggu')->count(),
            'sidang' => PendaftaranSidang::where('status', 'menunggu')->count(),
        ];

        // Approved title assignments, not examiner duties or proposed supervisors.
        // Distinct students avoid inflating workload across records or positions.
        $supervision = fn () => PengajuanJudul::query()
            ->selectRaw('COUNT(DISTINCT mahasiswa_id)')
            ->where('status', 'disetujui');
        $lecturers = User::query()->where('role', 'dosen')
            ->select('id', 'name', 'nim_nip')
            ->selectSub($supervision()->whereColumn('pembimbing_1_id', 'users.id'), 'bimbingan1')
            ->selectSub($supervision()->whereColumn('pembimbing_2_id', 'users.id'), 'bimbingan2')
            ->selectSub($supervision()->where(fn ($query) => $query
                ->whereColumn('pembimbing_1_id', 'users.id')
                ->orWhereColumn('pembimbing_2_id', 'users.id')), 'total')
            ->orderBy('name')->orderBy('id')->get()
            ->map(fn (User $dosen) => [
                'id' => $dosen->id,
                'name' => $dosen->name,
                'nip' => $dosen->nim_nip,
                'bimbingan1' => (int) $dosen->bimbingan1,
                'bimbingan2' => (int) $dosen->bimbingan2,
                'total' => (int) $dosen->total,
            ]);

        $pendingApprovals = PengajuanJudul::query()->where('status', 'menunggu')
            ->with(['mahasiswa:id,name,nim_nip,username', 'pembimbing1:id,name', 'pembimbing2:id,name'])
            ->oldest()->orderBy('id')->limit(5)->get()
            ->map(fn (PengajuanJudul $judul) => [
                'id' => $judul->id,
                'nama' => $judul->mahasiswa->name,
                'nim' => $judul->mahasiswa->nim_nip ?? $judul->mahasiswa->username,
                'judul' => $judul->judul_ta,
                'tanggal' => $judul->created_at->toDateString(),
                'pembimbing1' => $judul->pembimbing1?->name,
                'pembimbing2' => $judul->pembimbing2?->name,
            ]);

        return Inertia::render('Kaprodi/Dashboard', [
            'stats' => [
                'totalDosen' => $lecturers->count(),
                'totalMahasiswa' => User::where('role', 'mahasiswa')->count(),
                'antreanPersetujuan' => array_sum($pendingCounts),
            ],
            'pendingCounts' => $pendingCounts,
            'lecturers' => $lecturers,
            'pendingApprovals' => $pendingApprovals,
            // No persisted period/deadline source exists: null means unsupported.
            'deadlines' => null,
        ]);
    }
}
