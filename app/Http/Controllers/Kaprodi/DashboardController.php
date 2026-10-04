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
        $dosenCount = User::where('role', 'dosen')->count();
        $mahasiswaCount = User::where('role', 'mahasiswa')->count();
        $judulMenunggu = PengajuanJudul::where('status', 'menunggu')->count();
        $semproMenunggu = PendaftaranSempro::where('status', 'menunggu')->count();
        $sidangMenunggu = PendaftaranSidang::where('status', 'menunggu')->count();

        return Inertia::render('Kaprodi/Dashboard', [
            'stats' => [
                'totalDosen' => $dosenCount,
                'totalMahasiswa' => $mahasiswaCount,
                'antreanPersetujuan' => $judulMenunggu + $semproMenunggu + $sidangMenunggu,
            ],
        ]);
    }
}
