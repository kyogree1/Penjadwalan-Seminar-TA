<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\Penjadwalan;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $antreanSempro = PendaftaranSempro::where('status', 'menunggu')->count();
        $antreanSidang = PendaftaranSidang::where('status', 'menunggu')->count();
        $totalMahasiswa = User::where('role', 'mahasiswa')->count();
        $totalTerjadwal = Penjadwalan::where('status', 'terpublikasi')->count();

        return Inertia::render('Tendik/Dashboard', [
            'stats' => [
                'antreanVerifikasi' => $antreanSempro + $antreanSidang,
                'antreanSempro' => $antreanSempro,
                'antreanSidang' => $antreanSidang,
                'totalMahasiswa' => $totalMahasiswa,
                'totalTerjadwal' => $totalTerjadwal,
            ],
        ]);
    }
}
