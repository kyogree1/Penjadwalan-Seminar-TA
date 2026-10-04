<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\Penjadwalan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PendaftaranJadwalController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Daftar pendaftar sempro
        $registrants = PendaftaranSempro::with('mahasiswa:id,name,username')
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->mahasiswa?->name ?? 'Mahasiswa',
                    'nim' => $item->mahasiswa?->username ?? '-',
                    'registered' => $item->created_at->translatedFormat('d M Y'),
                    'wave' => (string) $item->gelombang,
                    'status' => match($item->status) {
                        'terjadwal' => 'Diterima',
                        'verifikasi_tendik' => 'Diverifikasi',
                        'ditolak' => 'Ditolak',
                        default => 'Menunggu',
                    },
                ];
            });

        // 2. Jadwal sempro & sidang
        $jadwalSempro = Penjadwalan::with(['mahasiswa:id,name,username', 'ruangan:id,nama_ruangan,gedung', 'pembimbing1:id,name', 'penguji1:id,name'])
            ->where('tipe', 'sempro')
            ->where('status', 'terpublikasi')
            ->latest('tanggal')
            ->get();

        $jadwalSidang = Penjadwalan::with(['mahasiswa:id,name,username', 'ruangan:id,nama_ruangan,gedung', 'pembimbing1:id,name', 'penguji1:id,name'])
            ->where('tipe', 'sidang')
            ->where('status', 'terpublikasi')
            ->latest('tanggal')
            ->get();

        return Inertia::render('Mahasiswa/PendaftaranJadwal', [
            'registrantsData' => $registrants,
            'jadwalSempro' => $jadwalSempro,
            'jadwalSidang' => $jadwalSidang,
        ]);
    }
}
