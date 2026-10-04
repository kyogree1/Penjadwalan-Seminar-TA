<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VerifikasiController extends Controller
{
    public function index(): Response
    {
        $semproList = PendaftaranSempro::with('mahasiswa:id,name,username,nim_nip,prodi')
            ->latest()
            ->get();

        $sidangList = PendaftaranSidang::with('mahasiswa:id,name,username,nim_nip,prodi')
            ->latest()
            ->get();

        return Inertia::render('Tendik/Verifikasi', [
            'dbSempro' => $semproList,
            'dbSidang' => $sidangList,
        ]);
    }

    public function verifySempro(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:verifikasi_tendik,ditolak,menunggu'],
            'catatan' => ['nullable', 'string'],
        ]);

        $sempro = PendaftaranSempro::findOrFail($id);
        $sempro->update($validated);

        return redirect()->back()->with('success', 'Verifikasi berkas Seminar Proposal berhasil disimpan!');
    }

    public function verifySidang(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:verifikasi_tendik,ditolak,menunggu'],
            'catatan' => ['nullable', 'string'],
        ]);

        $sidang = PendaftaranSidang::findOrFail($id);
        $sidang->update($validated);

        return redirect()->back()->with('success', 'Verifikasi berkas Sidang TA berhasil disimpan!');
    }
}
