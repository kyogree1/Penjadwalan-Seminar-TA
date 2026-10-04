<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PendaftaranSidangController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $sidang = PendaftaranSidang::where('mahasiswa_id', $user->id)
            ->latest()
            ->first();

        $pengajuanJudul = PengajuanJudul::where('mahasiswa_id', $user->id)
            ->where('status', 'disetujui')
            ->first();

        return Inertia::render('Pendaftaran/Sidang', [
            'sidang' => $sidang,
            'defaultJudul' => $pengajuanJudul?->judul_ta ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_ta' => ['required', 'string', 'max:255'],
            'lokasi_mitra' => ['nullable', 'string', 'max:255'],
            'skor_iaet' => ['nullable'],
            'skor_iaet_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'draft_laporan_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'turnitin_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $user = Auth::user();

        $uploaded = [];
        $fileFields = ['skor_iaet_file', 'draft_laporan_file', 'turnitin_file'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store("dokumen/sidang/{$user->id}", 'public');
                $uploaded[$field] = $path;
            }
        }

        PendaftaranSidang::updateOrCreate(
            ['mahasiswa_id' => $user->id],
            array_merge([
                'judul_ta' => $validated['judul_ta'],
                'lokasi_mitra' => $validated['lokasi_mitra'] ?? null,
                'gelombang' => 1,
                'status' => 'menunggu',
            ], $uploaded)
        );

        return redirect()->back()->with('success', 'Formulir pendaftaran Sidang Tugas Akhir berhasil diajukan!');
    }
}
