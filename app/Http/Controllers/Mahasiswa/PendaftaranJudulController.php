<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PendaftaranJudulController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        // Ambil riwayat pengajuan judul mahasiswa saat ini
        $pengajuan = PengajuanJudul::where('mahasiswa_id', $user->id)
            ->latest()
            ->first();

        // Ambil daftar dosen untuk dropdown
        $dosenList = User::where('role', 'dosen')
            ->select('id', 'name', 'username')
            ->get();

        return Inertia::render('Pendaftaran/Judul', [
            'pengajuan' => $pengajuan,
            'dosenList' => $dosenList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_ta' => ['required', 'string', 'max:255'],
            'bidang_penelitian' => ['required', 'string', 'max:100'],
            'pembimbing_1' => ['nullable'],
            'pembimbing_2' => ['nullable'],
            'telah_konsultasi' => ['boolean'],
        ]);

        $user = Auth::user();

        // Cari ID dosen jika dikirim nama atau ID
        $pemb1Id = is_numeric($validated['pembimbing_1'] ?? null) 
            ? (int) $validated['pembimbing_1'] 
            : User::where('role', 'dosen')->where('name', $validated['pembimbing_1'] ?? '')->value('id');

        $pemb2Id = is_numeric($validated['pembimbing_2'] ?? null) 
            ? (int) $validated['pembimbing_2'] 
            : User::where('role', 'dosen')->where('name', $validated['pembimbing_2'] ?? '')->value('id');

        PengajuanJudul::updateOrCreate(
            ['mahasiswa_id' => $user->id],
            [
                'judul_ta' => $validated['judul_ta'],
                'bidang_penelitian' => $validated['bidang_penelitian'],
                'pembimbing_1_id' => $pemb1Id,
                'pembimbing_2_id' => $pemb2Id,
                'telah_konsultasi' => $validated['telah_konsultasi'] ?? false,
                'status' => 'menunggu',
            ]
        );

        return redirect()->back()->with('success', 'Pengajuan judul tugas akhir berhasil disimpan!');
    }
}
