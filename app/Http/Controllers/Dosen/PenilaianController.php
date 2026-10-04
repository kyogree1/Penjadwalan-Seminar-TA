<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Penjadwalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PenilaianController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        // Cari seminar di mana dosen ini bertindak sebagai pembimbing atau penguji
        $jadwalUji = Penjadwalan::with([
            'mahasiswa:id,name,username,nim_nip',
            'ruangan:id,nama_ruangan',
        ])
            ->where(function ($q) use ($user) {
                $q->where('penguji_1_id', $user->id)
                    ->orWhere('penguji_2_id', $user->id)
                    ->orWhere('pembimbing_1_id', $user->id)
                    ->orWhere('pembimbing_2_id', $user->id);
            })
            ->latest('tanggal')
            ->get();

        return Inertia::render('Dosen/Penilaian', [
            'dbJadwalUji' => $jadwalUji,
        ]);
    }

    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'nilai' => ['required', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string'],
        ]);

        $penjadwalan = Penjadwalan::findOrFail($id);
        $penjadwalan->update([
            'catatan' => $validated['catatan'] ?? $penjadwalan->catatan,
            'status' => 'selesai',
        ]);

        return redirect()->back()->with('success', 'Nilai ujian dan evaluasi berhasil disimpan!');
    }
}
