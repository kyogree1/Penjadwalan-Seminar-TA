<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
            'pembimbing_1' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'dosen')],
            'pembimbing_2' => ['nullable', 'integer', 'different:pembimbing_1', Rule::exists('users', 'id')->where('role', 'dosen')],
            'telah_konsultasi' => ['boolean'],
        ]);

        $user = Auth::user();

        $pemb1Id = $validated['pembimbing_1'] ?? null;
        $pemb2Id = $validated['pembimbing_2'] ?? null;

        PengajuanJudul::updateOrCreate(
            ['mahasiswa_id' => $user->id],
            [
                'judul_ta' => $validated['judul_ta'],
                'bidang_penelitian' => $validated['bidang_penelitian'],
                'pembimbing_1_id' => $pemb1Id,
                'pembimbing_2_id' => $pemb2Id,
                'telah_konsultasi' => $validated['telah_konsultasi'] ?? false,
                'status' => 'menunggu',
                'disetujui_oleh' => null,
                'disetujui_at' => null,
            ]
        );

        return redirect()->back()->with('success', 'Pengajuan judul tugas akhir berhasil disimpan!');
    }
}
