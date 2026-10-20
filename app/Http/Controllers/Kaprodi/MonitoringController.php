<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringController extends Controller
{
    public function index(): Response
    {
        // ─── 1. Ringkasan Statistik Global ────────────────────────────────
        $totalMahasiswa = User::where('role', 'mahasiswa')->count();

        $progressCounts = [
            'pengajuan_judul' => PengajuanJudul::where('status', 'menunggu')->count(),
            'judul_disetujui' => PengajuanJudul::where('status', 'disetujui')->count(),
            'sempro_menunggu' => PendaftaranSempro::where('status', 'menunggu')->count(),
            'sempro_terjadwal' => PendaftaranSempro::where('status', 'terjadwal')->count(),
            'sempro_selesai' => PendaftaranSempro::where('status', 'selesai')->count(),
            'sidang_menunggu' => PendaftaranSidang::where('status', 'menunggu')->count(),
            'sidang_terjadwal' => PendaftaranSidang::where('status', 'terjadwal')->count(),
            'sidang_selesai' => PendaftaranSidang::where('status', 'selesai')->count(),
        ];

        // ─── 2. Rekap Bimbingan per Dosen ─────────────────────────────────
        // Hitung total sesi + sesi disetujui (delivered) per dosen pembimbing
        $bimbinganRekap = User::where('role', 'dosen')
            ->select('id', 'name', 'nim_nip')
            ->selectSub(
                Bimbingan::selectRaw('COUNT(*)')
                    ->whereColumn('dosen_id', 'users.id'),
                'total_sesi'
            )
            ->selectSub(
                Bimbingan::selectRaw('COUNT(*)')
                    ->whereColumn('dosen_id', 'users.id')
                    ->where('status', 'delivered'),
                'sesi_disetujui'
            )
            ->selectSub(
                PengajuanJudul::selectRaw('COUNT(DISTINCT mahasiswa_id)')
                    ->where('status', 'disetujui')
                    ->where(fn ($q) => $q
                        ->whereColumn('pembimbing_1_id', 'users.id')
                        ->orWhereColumn('pembimbing_2_id', 'users.id')),
                'total_bimbingan'
            )
            ->orderBy('name')
            ->get()
            ->map(fn (User $d) => [
                'id'              => $d->id,
                'name'            => $d->name,
                'nip'             => $d->nim_nip,
                'total_bimbingan' => (int) $d->total_bimbingan,
                'total_sesi'      => (int) $d->total_sesi,
                'sesi_disetujui'  => (int) $d->sesi_disetujui,
            ]);

        // ─── 3. Daftar Mahasiswa & Status Tahap TA ────────────────────────
        $mahasiswaList = User::where('role', 'mahasiswa')
            ->select('id', 'name', 'username', 'nim_nip')
            ->with([
                'pengajuanJudul:id,mahasiswa_id,judul_ta,status,pembimbing_1_id,pembimbing_2_id',
                'pengajuanJudul.pembimbing1:id,name',
                'pengajuanJudul.pembimbing2:id,name',
            ])
            ->orderBy('name')
            ->get()
            ->map(function (User $mhs) {
                $judul = $mhs->pengajuanJudul->first();
                return [
                    'id'           => $mhs->id,
                    'nama'         => $mhs->name,
                    'nim'          => $mhs->nim_nip ?? $mhs->username,
                    'judul'        => $judul?->judul_ta,
                    'status_judul' => $judul?->status ?? 'belum_mengajukan',
                    'pembimbing1'  => $judul?->pembimbing1?->name,
                    'pembimbing2'  => $judul?->pembimbing2?->name,
                ];
            });

        // ─── 4. Jadwal Aktif (Terpublikasi) ───────────────────────────────
        $jadwalAktif = Penjadwalan::with([
            'mahasiswa:id,name,nim_nip,username',
            'pembimbing1:id,name',
            'penguji1:id,name',
        ])
            ->where('status', 'terpublikasi')
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn (Penjadwalan $j) => [
                'id'          => $j->id,
                'tipe'        => $j->tipe,
                'tanggal'     => $j->tanggal?->toDateString(),
                'jam_mulai'   => $j->jam_mulai,
                'jam_selesai' => $j->jam_selesai,
                'mahasiswa'   => $j->mahasiswa?->name,
                'nim'         => $j->mahasiswa?->nim_nip ?? $j->mahasiswa?->username,
                'pembimbing'  => $j->pembimbing1?->name,
                'penguji'     => $j->penguji1?->name,
                'gelombang'   => $j->gelombang,
            ]);

        return Inertia::render('Koordinator/Monitoring', [
            'totalMahasiswa' => $totalMahasiswa,
            'progressCounts' => $progressCounts,
            'bimbinganRekap' => $bimbinganRekap,
            'mahasiswaList'  => $mahasiswaList,
            'jadwalAktif'    => $jadwalAktif,
        ]);
    }
}
