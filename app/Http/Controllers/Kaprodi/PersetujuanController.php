<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PersetujuanController extends Controller
{
    public function index(): Response
    {
        $judulList = PengajuanJudul::with([
            'mahasiswa:id,name,username,nim_nip',
            'pembimbing1:id,name',
            'pembimbing2:id,name',
        ])
            ->latest()
            ->get();

        $semproList = PendaftaranSempro::with('mahasiswa:id,name,username,nim_nip')
            ->latest()
            ->get();

        $sidangList = PendaftaranSidang::with('mahasiswa:id,name,username,nim_nip')
            ->latest()
            ->get();

        return Inertia::render('Kaprodi/Persetujuan', [
            'dbJudul' => $judulList,
            'dbSempro' => $semproList,
            'dbSidang' => $sidangList,
        ]);
    }

    public function updateJudul(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,menunggu,disetujui,ditolak,revisi'],
            'catatan_kaprodi' => ['nullable', 'string'],
            'pembimbing_1_id' => ['nullable'],
            'pembimbing_2_id' => ['nullable'],
        ]);

        $judul = PengajuanJudul::findOrFail($id);
        $user = Auth::user();

        $judul->update([
            'status' => $validated['status'],
            'catatan_kaprodi' => $validated['catatan_kaprodi'] ?? null,
            'disetujui_oleh' => $user->id,
            'disetujui_at' => now(),
            'pembimbing_1_id' => $validated['pembimbing_1_id'] ?? $judul->pembimbing_1_id,
            'pembimbing_2_id' => $validated['pembimbing_2_id'] ?? $judul->pembimbing_2_id,
        ]);

        return redirect()->back()->with('success', 'Keputusan persetujuan judul berhasil disimpan!');
    }

    public function updateSempro(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'catatan' => ['nullable', 'string'],
        ]);

        $sempro = PendaftaranSempro::findOrFail($id);
        $sempro->update($validated);

        return redirect()->back()->with('success', 'Status pendaftaran Sempro berhasil diperbarui!');
    }

    public function updateSidang(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'catatan' => ['nullable', 'string'],
        ]);

        $sidang = PendaftaranSidang::findOrFail($id);
        $sidang->update($validated);

        return redirect()->back()->with('success', 'Status pendaftaran Sidang TA berhasil diperbarui!');
    }
}
