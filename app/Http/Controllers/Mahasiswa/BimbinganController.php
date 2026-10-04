<?php

namespace App\Http\Controllers\Mahasiswa;

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

        $bimbinganList = Bimbingan::with('dosen:id,name')
            ->where('mahasiswa_id', $user->id)
            ->latest('tanggal')
            ->get();

        $dosenList = User::where('role', 'dosen')
            ->select('id', 'name')
            ->get();

        return Inertia::render('Pendaftaran/Bimbingan', [
            'bimbinganList' => $bimbinganList,
            'dosenList' => $dosenList,
        ]);
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

        if (!$dosenId) {
            // Default ke dosen pertama jika tidak cocok
            $dosenId = User::where('role', 'dosen')->value('id') ?? 1;
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
