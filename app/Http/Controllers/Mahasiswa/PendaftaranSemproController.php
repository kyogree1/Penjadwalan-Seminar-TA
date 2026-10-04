<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PengajuanJudul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PendaftaranSemproController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $sempro = PendaftaranSempro::where('mahasiswa_id', $user->id)
            ->latest()
            ->first();

        $pengajuanJudul = PengajuanJudul::where('mahasiswa_id', $user->id)
            ->where('status', 'disetujui')
            ->first();

        return Inertia::render('Pendaftaran/Sempro', [
            'sempro' => $sempro,
            'defaultJudul' => $pengajuanJudul?->judul_ta ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_ta' => ['required', 'string', 'max:255'],
            'bentuk_ta' => ['required', 'string', 'max:150'],
            'lokasi_mitra' => ['nullable', 'string', 'max:255'],
            'skor_iaet' => ['nullable'],
            'lembar_kehadiran_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'proposal_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'turnitin_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'iaet_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $user = Auth::user();

        // Upload berkas jika ada
        $uploaded = [];
        $fileFields = ['lembar_kehadiran_file', 'proposal_file', 'turnitin_file', 'iaet_file'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store("dokumen/sempro/{$user->id}", 'public');
                $uploaded[$field] = $path;
            }
        }

        PendaftaranSempro::updateOrCreate(
            ['mahasiswa_id' => $user->id],
            array_merge([
                'judul_ta' => $validated['judul_ta'],
                'bentuk_ta' => $validated['bentuk_ta'],
                'lokasi_mitra' => $validated['lokasi_mitra'] ?? null,
                'gelombang' => 2,
                'status' => 'menunggu',
            ], $uploaded)
        );

        return redirect()->back()->with('success', 'Formulir pendaftaran Seminar Proposal berhasil diajukan!');
    }
}
