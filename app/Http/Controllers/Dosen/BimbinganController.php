<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\PengajuanJudul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BimbinganController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        // Daftar mahasiswa yang muncul adalah mahasiswa yang memilih/menugaskan dosen yang bersangkutan sebagai pembimbing
        $titles = PengajuanJudul::with(['mahasiswa:id,name,username,nim_nip,role', 'pembimbing1:id,name', 'pembimbing2:id,name'])
            ->where('status', 'disetujui')
            ->where(function ($query) use ($user) {
                $query->where('pembimbing_1_id', $user->id)
                    ->orWhere('pembimbing_2_id', $user->id);
            })
            ->whereHas('mahasiswa', fn ($query) => $query->where('role', 'mahasiswa'))
            ->orderByDesc('id')->get()->unique('mahasiswa_id');

        // Sesi bimbingan yang dilakukan bersama dosen ini
        $sessions = Bimbingan::where('dosen_id', $user->id)
            ->whereIn('mahasiswa_id', $titles->pluck('mahasiswa_id'))
            ->orderBy('tanggal')->orderBy('id')->get()->groupBy('mahasiswa_id');

        $advisees = $titles->map(fn ($title) => [
            'id' => $title->mahasiswa_id,
            'nama' => $title->mahasiswa->name,
            'nim' => $title->mahasiswa->nim_nip ?? $title->mahasiswa->username,
            'judul' => $title->judul_ta,
            'roleAs' => $title->pembimbing_1_id === $user->id ? 'Pembimbing 1' : 'Pembimbing 2',
            'pembimbing1' => $title->pembimbing1?->name ?? '—',
            'pembimbing2' => $title->pembimbing2?->name,
            'sesiList' => ($sessions->get($title->mahasiswa_id) ?? collect())->map(fn ($session, $index) => [
                'id' => $session->id,
                'no' => $index + 1,
                'tanggal' => $session->tanggal->format('Y-m-d'),
                'rangkuman' => $session->rangkuman,
                'keterangan' => $session->keterangan,
                'status' => $session->status,
                'catatanDosen' => $session->catatan_dosen,
            ])->values()->all(),
        ])->sortBy('nama')->values();

        return Inertia::render('Dosen/Bimbingan', ['advisees' => $advisees]);
    }

    public function paraf(Request $request, $id)
    {
        $user = Auth::user();
        $bimbingan = Bimbingan::where('dosen_id', $user->id)->find($id);

        if (!$bimbingan) {
            return redirect()->back()->with('success', 'Catatan pembimbing dan paraf berhasil disimpan!');
        }

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

        return redirect()->back()->with('success', 'Catatan pembimbing dan paraf berhasil disimpan!');
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
            'keterangan' => $validated['keterangan'] ?? 'Konsultasi Bimbingan',
            'catatan_dosen' => $validated['catatan_dosen'] ?? null,
            'status' => 'delivered',
        ]);

        return redirect()->back()->with('success', 'Sesi bimbingan berhasil dicatat!');
    }
}
