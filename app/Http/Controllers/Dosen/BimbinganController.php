<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\PengajuanJudul;
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
            'status' => ['required', 'in:delivered,disetujui,process,revisi,ditolak'],
            'catatan_dosen' => ['nullable', 'string'],
        ]);

        $dbStatus = match ($validated['status']) {
            'disetujui', 'delivered' => 'delivered',
            'ditolak' => 'ditolak',
            default => 'process',
        };

        $bimbingan->update([
            'status' => $dbStatus,
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

        $mahasiswa = User::findOrFail($validated['mahasiswa_id']);
        $isSupervisee = PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'disetujui')
            ->where(function ($query) use ($user) {
                $query->where('pembimbing_1_id', $user->id)
                    ->orWhere('pembimbing_2_id', $user->id);
            })
            ->exists();

        abort_unless($mahasiswa->isMahasiswa() && $isSupervisee, 403);

        Bimbingan::create([
            'mahasiswa_id' => $validated['mahasiswa_id'],
            'dosen_id' => $user->id,
            'tanggal' => $validated['tanggal'],
            'rangkuman' => $validated['rangkuman'],
            'keterangan' => $validated['keterangan'] ?? 'Sesi bimbingan resmi',
            'catatan_dosen' => $validated['catatan_dosen'] ?? 'Disetujui pembimbing',
            'status' => 'delivered',
        ]);

        return redirect()->back()->with('success', 'Sesi bimbingan baru berhasil dicatat dan diparaf!');
    }
}
