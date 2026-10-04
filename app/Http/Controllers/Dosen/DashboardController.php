<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Penjadwalan;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        // Cari sesi bimbingan menunggu paraf
        $pendingBimbingan = Bimbingan::where('pembimbing_id', $user->id)
            ->where('status_paraf', 'menunggu')
            ->count();

        // Cari jadwal menguji mendatang
        $jadwalUjiCount = Penjadwalan::where(function ($q) use ($user) {
            $q->where('penguji_1_id', $user->id)
                ->orWhere('penguji_2_id', $user->id);
        })
            ->where('tanggal', '>=', now()->toDateString())
            ->count();

        return Inertia::render('Dosen/Dashboard', [
            'stats' => [
                'pendingBimbingan' => $pendingBimbingan,
                'jadwalUjiCount' => $jadwalUjiCount,
            ],
        ]);
    }
}
