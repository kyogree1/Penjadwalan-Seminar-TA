<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BimbinganController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $bimbinganList = Bimbingan::with('dosen:id,name')
            ->where('mahasiswa_id', $user->id)
            ->latest('tanggal')
            ->get();

        $dosenList = User::where('role', 'dosen')
            ->whereIn('id', $this->supervisorIds($user->id))
            ->select('id', 'name')
            ->get();

        return Inertia::render('Pendaftaran/Bimbingan', [
            'bimbinganList' => $bimbinganList,
            'dosenList' => $dosenList,
        ]);
    }

    // This is an assigned-supervisor logbook, not a pre-assignment consultation queue.
    private function supervisorIds(int $studentId): array
    {
        return PengajuanJudul::where('mahasiswa_id', $studentId)->where('status', 'disetujui')
            ->get(['pembimbing_1_id', 'pembimbing_2_id'])
            ->flatMap(fn ($title) => [$title->pembimbing_1_id, $title->pembimbing_2_id])
            ->filter()->unique()->values()->all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'dosen' => ['required'],
            'rangkuman' => ['required', 'string'],
            'keterangan' => ['required', 'string'],
        ]);

        $user = Auth::user();

        // Cari dosen
        $dosenId = is_numeric($validated['dosen'])
            ? (int) $validated['dosen']
            : User::where('role', 'dosen')->where('name', $validated['dosen'])->value('id');

        if (! $dosenId || ! in_array($dosenId, $this->supervisorIds($user->id)) || ! User::where('role', 'dosen')->whereKey($dosenId)->exists()) {
            throw ValidationException::withMessages(['dosen' => 'Logbook hanya untuk pembimbing pada judul yang disetujui.']);
        }

        Bimbingan::create([
            'mahasiswa_id' => $user->id,
            'dosen_id' => $dosenId,
            'tanggal' => $validated['tanggal'],
            'rangkuman' => $validated['rangkuman'],
            'keterangan' => $validated['keterangan'],
            'status' => 'process',
        ]);

        return redirect()->back()->with('success', 'Logbook bimbingan berhasil ditambahkan!');
    }
}
