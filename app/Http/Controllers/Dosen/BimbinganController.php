<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BimbinganController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        // Ambil riwayat bimbingan mahasiswa yang dibimbing oleh dosen ini
        $bimbinganList = Bimbingan::with('mahasiswa:id,name,username,nim_nip,prodi')
            ->where('dosen_id', $user->id)
            ->latest('tanggal')
            ->get();

        return Inertia::render('Dosen/Bimbingan', [
            'dbBimbinganList' => $bimbinganList,
        ]);
    }

    public function paraf(Request $request, $id)
    {
        $user = Auth::user();
        $bimbingan = Bimbingan::where('dosen_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:disetujui,revisi,ditolak'],
            'catatan_dosen' => ['nullable', 'string'],
        ]);

        $bimbingan->update([
            'status' => $validated['status'],
            'catatan_dosen' => $validated['catatan_dosen'] ?? $bimbingan->catatan_dosen,
        ]);

        return redirect()->back()->with('success', 'Paraf digital dan catatan bimbingan berhasil disimpan!');
    }

    public function storeSesi(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'mahasiswa_id' => ['required', 'exists:users,id'],
            'tanggal' => ['required', 'date'],
            'rangkuman' => ['required', 'string'],
            'keterangan' => ['nullable', 'string'],
            'catatan_dosen' => ['nullable', 'string'],
        ]);

        Bimbingan::create([
            'mahasiswa_id' => $validated['mahasiswa_id'],
            'dosen_id' => $user->id,
            'tanggal' => $validated['tanggal'],
            'rangkuman' => $validated['rangkuman'],
            'keterangan' => $validated['keterangan'] ?? 'Sesi bimbingan resmi',
            'catatan_dosen' => $validated['catatan_dosen'] ?? 'Disetujui pembimbing',
            'status' => 'disetujui',
        ]);

        return redirect()->back()->with('success', 'Sesi bimbingan baru berhasil dicatat dan diparaf!');
    }
}
